import {
  Case,
  CaseGroupID,
  Group,
  InLine,
  MultipleCaseAdd,
  RootState,
} from '../types';
import { Module } from 'vuex';
import { NIL, v4 } from 'uuid';
// import T from '../../../lang';
export interface CasesState {
  groups: Group[];
  selected: CaseGroupID;
  layout: InLine[];
  hide: boolean;
}

export const casesStore: Module<CasesState, RootState> = {
  namespaced: true,
  state: {
    groups: [
      {
        groupId: NIL,
        name: 'sin_grupo',
        defined: false,
        points: 100,
        cases: [],
      },
    ],
    selected: {
      caseId: NIL,
      groupId: NIL,
    },
    layout: [],
    hide: false,
  },
  mutations: {
    resetStore(state) {
      state.groups = [
        {
          groupId: NIL,
          name: 'sin_grupo',
          defined: false,
          points: 100,
          cases: [],
        },
      ];
      state.selected = {
        caseId: NIL,
        groupId: NIL,
      };
      state.layout = [];
      state.hide = false;
    },
    addCase(state, payload: Case) {
      const group = state.groups.find(
        (group) => group.groupId === payload.groupId,
      );
      if (group) {
        group.cases.push(payload);
      }
      state = calculatePoints(state);
    },
  },
  actions: {
    addMultipleCases({ commit, getters, state }, payload: MultipleCaseAdd) {
      const cases: Case[] = getters.getCasesFromGroup(payload.groupId);
      if (cases !== undefined) {
        let caseNumber = 0;
        for (let i = 0; i < payload.number; i++) {
          let shouldBreak = false;
          do {
            caseNumber++;
            const name = payload.prefix + caseNumber + payload.suffix;
            const caseExist = cases.find((_case) => _case.name === name);
            if (caseExist === undefined) {
              const layoutWithNewIds = state.layout.map((layoutLine) => {
                return { ...layoutLine, lineId: v4() };
              });
              const casePayload: Case = {
                name: name,
                defined: false,
                points: 0,
                caseId: v4(),
                groupId: payload.groupId,
                lines: layoutWithNewIds,
              };
              commit('addCase', casePayload);
              shouldBreak = true;
            }
          } while (!shouldBreak);
        }
      }
    },
    setLines({ getters }, payload: InLine[]) {
      const selectedCase: Case = getters.getSelectedCase;
      selectedCase.lines = payload;
    },
    addNewLine({ getters }) {
      const selectedCase: Case = getters.getSelectedCase;
      const newLine: InLine = {
        lineId: v4(),
        label: 'NEW',
        value: '',
        type: 'line',
        arrayData: { size: 0, min: 0, max: 0, distinct: false, arrayVal: '' },
        matrixData: {},
      };
      selectedCase.lines.push(newLine);
    },
    updateLine({ getters }, payload: InLine) {
      const selectedCase: Case = getters.getSelectedCase;
      const lineToUpdate = selectedCase.lines.find(
        (line) => line.lineId === payload.lineId,
      );
      if (lineToUpdate !== undefined) {
        lineToUpdate.value = payload.value;
        lineToUpdate.type = payload.type;
        lineToUpdate.label = payload.label;
        lineToUpdate.arrayData = payload.arrayData;
        lineToUpdate.matrixData = payload.matrixData;
      }
    },
    deleteLine({ getters }, payload: string) {
      const selectedCase: Case = getters.getSelectedCase;
      selectedCase.lines = selectedCase.lines.filter(
        (line) => line.lineId !== payload,
      );
    },
  },
  getters: {
    getCasesFromGroup: (state) => (groupId: string) => {
      return state.groups.find((group) => group.groupId === groupId)?.cases;
    },
    getGroupIdsAndNames: (state) => {
      return state.groups.map((group) => {
        return { value: group.groupId, text: group.name };
      });
    },
    getAllCases: (state) => {
      return state.groups.reduce((cases: Case[], currCase) => {
        return [...cases, ...currCase.cases];
      }, []);
    },
    getSelectedCase: (state) => {
      const selectedGroup = state.groups.find(
        (group) => group.groupId === state.selected.groupId,
      );
      if (selectedGroup !== undefined) {
        return selectedGroup.cases.find(
          (_case) => _case.caseId === state.selected.caseId,
        );
      }
      return undefined;
    },
    getSelectedGroup: (state) => {
      return state.groups.find(
        (group) => group.groupId === state.selected.groupId,
      );
    },
  },
};

export function calculatePoints(state: CasesState) {
  let maxPoints = 100;
  let notDefinedCount = 0;

  state.groups.forEach((element) => {
    if (element.groupId === NIL) {
      // Calculate points of cases without group
      element.cases.forEach((caseElement) => {
        if (caseElement.defined) {
          maxPoints -= caseElement.points ? caseElement.points : 0;
        } else {
          notDefinedCount++;
        }
      });
    } else {
      if (element.defined) {
        maxPoints -= element.points ? element.points : 0;
      } else {
        notDefinedCount++;
      }
    }
  });

  const individualPoints = maxPoints / notDefinedCount;

  state.groups = state.groups.map((element) => {
    if (element.groupId === NIL) {
      element.cases = element.cases.map((caseElement) => {
        if (!caseElement.defined) {
          caseElement.points = individualPoints;
        }
        return caseElement;
      });
    }
    if (!element.defined) {
      element.points = individualPoints;
    }
    return element;
  });

  return state;
}
