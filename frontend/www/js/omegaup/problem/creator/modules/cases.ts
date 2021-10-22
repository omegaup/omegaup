import {
  Case,
  CaseGroupID,
  Group,
  CaseLine,
  RootState,
  GroupID,
  LineID,
} from '../types';
import { Module } from 'vuex';
import { NIL as UUID_NIL, v4 as uuid } from 'uuid';
import T from '../../../lang';
import Vue from 'vue';
export interface CasesState {
  groups: Group[];
  selected: CaseGroupID;
  layout: CaseLine[];
  hide: boolean;
}

export const casesStore: Module<CasesState, RootState> = {
  namespaced: true,
  state: {
    groups: [
      {
        groupID: UUID_NIL,
        name: T.problemCreatorNoGroup,
        pointsDefined: false,
        points: 100,
        cases: [],
      },
    ],
    selected: {
      caseID: UUID_NIL,
      groupID: UUID_NIL,
    },
    layout: [],
    hide: false,
  },
  mutations: {
    resetStore(state) {
      state.groups = [
        {
          groupID: UUID_NIL,
          name: T.problemCreatorNoGroup,
          pointsDefined: false,
          points: 100,
          cases: [],
        },
      ];
      state.selected = {
        caseID: UUID_NIL,
        groupID: UUID_NIL,
      };
      state.layout = [];
      state.hide = false;
    },
    addGroup(state, newGroup: Group) {
      state.groups.push(newGroup);
      state = assignMissingPoints(state);
    },
    addCase(state, newCase: Case) {
      const group = state.groups.find(
        (group) => group.groupID === newCase.groupID,
      );
      if (group) {
        group.cases.push(newCase);
      }
      state = assignMissingPoints(state);
    },
    editGroup(state, groupData: Group) {
      const groupTarget = state.groups.find(
        (group) => group.groupID === groupData.groupID,
      );
      if (groupTarget) {
        groupTarget.points = groupData.points;
        groupTarget.name = groupData.name;
        groupTarget.pointsDefined = groupData.pointsDefined;
      }
      state = assignMissingPoints(state);
    },
    editCase(
      state,
      { oldGroupID, editedCase }: { oldGroupID: GroupID; editedCase: Case },
    ) {
      // Old group

      // Find original case
      const groupTarget = state.groups.find(
        (group) => group.groupID === oldGroupID,
      );

      const caseIndex =
        groupTarget?.cases.findIndex(
          (_case) => _case.caseID === editedCase.caseID,
        ) ?? -1;

      if (caseIndex === -1) {
        return;
      }

      const caseTarget = groupTarget?.cases[caseIndex];

      if (caseTarget?.groupID !== editedCase.groupID) {
        // If we changed the group
        if (groupTarget) {
          groupTarget.cases = groupTarget.cases.filter(
            (_case) => _case.caseID !== editedCase.caseID,
          );
          // Find the new group
          const newGroup = state.groups.find(
            (group) => group.groupID === editedCase.groupID,
          );
          newGroup?.cases.push(editedCase);
          state.selected.groupID = editedCase.groupID;
        }
      } else {
        if (caseTarget && groupTarget) {
          Vue.set(groupTarget.cases, caseIndex, {
            ...caseTarget,
            ...editedCase,
          });
        }
      }
      state = assignMissingPoints(state);
    },
    deleteGroup(state, groupIDToBeDeleted: GroupID) {
      state.groups = state.groups.filter(
        (group) => group.groupID !== groupIDToBeDeleted,
      );
      state = assignMissingPoints(state);
    },
    deleteCase(state, caseGroupIDToBeDeleted: CaseGroupID) {
      const groupTarget = state.groups.find(
        (group) => group.groupID == caseGroupIDToBeDeleted.groupID,
      );
      if (groupTarget) {
        groupTarget.cases = groupTarget.cases.filter(
          (_case) => _case.caseID !== caseGroupIDToBeDeleted.caseID,
        );
      }
      state.selected.caseID = UUID_NIL;
      state.selected.groupID = UUID_NIL;
      state = assignMissingPoints(state);
    },
    deleteGroupCases(state, groupIDToBeDeleted: GroupID) {
      const groupTarget = state.groups.find(
        (group) => group.groupID === groupIDToBeDeleted,
      );
      if (groupTarget) {
        groupTarget.cases = [];
      }
      state = assignMissingPoints(state);
    },
    addLayoutLine(state) {
      const payload: CaseLine = {
        lineID: uuid(),
        label: 'NEW',
        data: {
          kind: 'line',
          value: '',
        },
      };
      state.layout.push(payload);
    },
    editLayoutLine(state, layoutLineData: CaseLine) {
      const lineToEdit = state.layout.find(
        (line) => line.lineID === layoutLineData.lineID,
      );
      if (lineToEdit) {
        lineToEdit.data.kind = layoutLineData.data.kind;
        lineToEdit.label = layoutLineData.label;
      }
    },
    removeLayoutLine(state, lineIDToBeDeleted: LineID) {
      state.layout = state.layout.filter(
        (line) => line.lineID !== lineIDToBeDeleted,
      );
    },
    setLayout(state, layoutLines: CaseLine[]) {
      state.layout = layoutLines;
    },
    setSelected(state, CaseGroupsIDToBeSelected: CaseGroupID) {
      state.selected = CaseGroupsIDToBeSelected;
    },
    toggleHide(state) {
      state.hide = !state.hide;
    },
  },
  actions: {},
};

export function assignMissingPoints(state: CasesState): CasesState {
  let maxPoints = 100;
  let notDefinedCount = 0;

  for (const group of state.groups) {
    if (group.groupID === UUID_NIL) {
      // Calculate points of cases without group
      for (const caseElement of group.cases) {
        if (caseElement.pointsDefined) {
          maxPoints -= caseElement?.points ?? 0;
        } else {
          notDefinedCount++;
        }
      }
    } else {
      if (group.pointsDefined) {
        maxPoints -= group?.points ?? 0;
      } else {
        notDefinedCount++;
      }
    }
  }

  const individualPoints = maxPoints / notDefinedCount;

  state.groups = state.groups.map((element) => {
    if (element.groupID === UUID_NIL) {
      element.cases = element.cases.map((caseElement) => {
        if (!caseElement.pointsDefined) {
          caseElement.points = individualPoints;
        }
        return caseElement;
      });
    }
    if (!element.pointsDefined) {
      element.points = individualPoints;
    }
    return element;
  });

  return state;
}
