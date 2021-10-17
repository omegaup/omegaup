import { Case, CaseGroupID, Group, InLine, RootState } from '../types';
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
    addGroup(state, payload: Group) {
      state.groups.push(payload);
      state = calculatePoints(state);
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
    editGroup(state, payload: Group) {
      const groupTarget = state.groups.find(
        (group) => group.groupId === payload.groupId,
      );
      if (groupTarget !== undefined) {
        groupTarget.points = payload.points;
        groupTarget.name = payload.name;
        groupTarget.defined = payload.defined;
      }
      state = calculatePoints(state);
    },
    editCase(state, payload: { oldGroup: string; editedCase: Case }) {
      // Old group

      // Find original case
      const groupTarget = state.groups.find(
        (group) => group.groupId === payload.oldGroup,
      );

      const caseTarget = groupTarget?.cases.find(
        (_case) => _case.caseId === payload.editedCase.caseId,
      );

      if (caseTarget?.groupId !== payload.editedCase.groupId) {
        // If we changed the group
        if (groupTarget !== undefined) {
          groupTarget.cases = groupTarget.cases.filter(
            (_case) => _case.caseId !== payload.editedCase.caseId,
          );
          // Find the new group
          const newGroup = state.groups.find(
            (group) => group.groupId === payload.editedCase.groupId,
          );
          newGroup?.cases.push(payload.editedCase);
          state.selected.groupId = payload.editedCase.groupId;
        }
      } else {
        if (caseTarget !== undefined) {
          caseTarget.groupId = payload.editedCase.groupId;
          caseTarget.points = payload.editedCase.points;
          caseTarget.defined = payload.editedCase.defined;
          caseTarget.name = payload.editedCase.name;
        }
      }
      state = calculatePoints(state);
    },
    deleteGroup(state, payload: string) {
      state.groups = state.groups.filter((group) => group.groupId !== payload);
      state = calculatePoints(state);
    },
    deleteCase(state, payload: CaseGroupID) {
      const groupTarget = state.groups.find(
        (group) => group.groupId == payload.groupId,
      );
      if (groupTarget !== undefined) {
        groupTarget.cases = groupTarget.cases.filter(
          (_case) => _case.caseId !== payload.caseId,
        );
      }
      state.selected.caseId = NIL;
      state.selected.groupId = NIL;
      state = calculatePoints(state);
    },
    deleteGroupCases(state, payload: string) {
      const groupTarget = state.groups.find(
        (group) => group.groupId === payload,
      );
      if (groupTarget !== undefined) {
        groupTarget.cases = [];
      }
      state = calculatePoints(state);
    },
    addLayoutLine(state) {
      const payload: InLine = {
        lineId: v4(),
        label: 'NEW',
        value: '',
        type: 'line',
        arrayData: { size: 0, min: 0, max: 0, distinct: false, arrayVal: '' },
        matrixData: {},
      };
      state.layout.push(payload);
    },
    editLayoutLine(state, payload: InLine) {
      const lineToEdit = state.layout.find(
        (line) => line.lineId === payload.lineId,
      );
      if (lineToEdit !== undefined) {
        lineToEdit.type = payload.type;
        lineToEdit.label = payload.label;
      }
    },
    removeLayoutLine(state, payload: string) {
      state.layout = state.layout.filter((line) => line.lineId !== payload);
    },
    setLayout(state, payload: InLine[]) {
      state.layout = payload;
    },
    setSelected(state, payload: CaseGroupID) {
      state.selected = payload;
    },
    toggleHide(state) {
      state.hide = !state.hide;
    },
  },
  actions: {},
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
