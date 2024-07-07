import {
  Case,
  CaseGroupID,
  Group,
  CaseLine,
  RootState,
  GroupID,
  LineID,
  CaseRequest,
  MultipleCaseAddRequest,
  CaseID,
} from '../types';
import { Module } from 'vuex';
import { NIL as UUID_NIL, v4 as uuid } from 'uuid';
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
    groups: [],
    selected: {
      caseID: UUID_NIL,
      groupID: UUID_NIL,
    },
    layout: [],
    hide: false,
  },
  mutations: {
    resetStore(state) {
      state.groups = [];
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
    editGroup(state, groupData: Group) {
      const groupTarget = state.groups.find(
        (group) => group.groupID === groupData.groupID,
      );
      if (groupTarget) {
        groupTarget.points = groupData.points;
        groupTarget.name = groupData.name;
        groupTarget.autoPoints = groupData.autoPoints;
      }
      state = assignMissingPoints(state);
    },
    addCase(state, caseRequest: CaseRequest) {
      if (caseRequest.groupID === UUID_NIL) {
        // Should create a new group with the same name
        const newCase = generateCase({
          name: caseRequest.name,
          caseID: caseRequest.caseID,
        });
        const groupID = uuid();
        const newGroup = generateGroup({
          name: caseRequest.name,
          groupID,
          points: caseRequest.points,
          autoPoints: caseRequest.autoPoints,
          ungroupedCase: true,
          cases: [{ ...newCase, groupID }],
        });
        state.groups.push(newGroup);
      } else {
        const group = state.groups.find(
          (group) => group.groupID === caseRequest.groupID,
        );
        if (!group) {
          return;
        }

        group.cases.push(
          generateCase({
            name: caseRequest.name,
            groupID: caseRequest.groupID,
            caseID: caseRequest.caseID,
            points: caseRequest.points,
          }),
        );
      }
      state = assignMissingPoints(state);
    },
    editCase(
      state,
      {
        oldGroupID,
        editedCase,
      }: { oldGroupID: GroupID; editedCase: Required<CaseRequest> },
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
          // If the group has the same name as the case. I.e. the case doesn't have any group
          if (groupTarget.ungroupedCase) {
            state.groups = state.groups.filter(
              (group) => group.groupID !== groupTarget.groupID,
            );
          }
          // Find the new group
          const newGroup = state.groups.find(
            (group) => group.groupID === editedCase.groupID,
          );
          newGroup?.cases.push(
            generateCase({
              name: editedCase.name,
              groupID: newGroup.groupID,
              caseID: editedCase.caseID,
              lines: editedCase.lines,
            }),
          );
          state.selected.groupID = editedCase.groupID;
        }
      } else {
        if (caseTarget && groupTarget) {
          if (groupTarget.ungroupedCase) {
            // Update both case and group
            groupTarget.name = editedCase.name;
            groupTarget.points = editedCase.points;
            groupTarget.autoPoints = editedCase.autoPoints;
          }
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
    deleteUngroupedCases(state) {
      state.groups = state.groups.filter(
        (group) => group.ungroupedCase === false,
      );
      state = assignMissingPoints(state);
    },
    deleteCase(state, caseGroupIDToBeDeleted: CaseGroupID) {
      const groupTarget = state.groups.find(
        (group) => group.groupID == caseGroupIDToBeDeleted.groupID,
      );
      if (groupTarget) {
        if (groupTarget.ungroupedCase) {
          // Delete the entire group
          state.groups = state.groups.filter(
            (group) => group.groupID !== groupTarget.groupID,
          );
        } else {
          groupTarget.cases = groupTarget.cases.filter(
            (_case) => _case.caseID !== caseGroupIDToBeDeleted.caseID,
          );
        }
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
  actions: {
    addMultipleCases(
      { commit, getters, state },
      multipleCaseRequest: MultipleCaseAddRequest,
    ) {
      // This set will store all occupied names in the given group
      const usedCaseNames: Set<string> = new Set();

      if (multipleCaseRequest.groupID === UUID_NIL) {
        // We should add group names
        for (const group of state.groups) {
          usedCaseNames.add(group.name);
        }
      } else {
        // We should add case names
        const cases: Case[] = getters.getCasesFromGroup(
          multipleCaseRequest.groupID,
        );
        if (cases) {
          for (const case_ of cases) {
            usedCaseNames.add(case_.name);
          }
        }
      }

      let caseNumber = 0;
      for (let i = 0; i < multipleCaseRequest.numberOfCases; i++) {
        let caseName = '';
        do {
          caseNumber++;
          caseName = `${multipleCaseRequest.prefix}${caseNumber}${multipleCaseRequest.suffix}`;
        } while (usedCaseNames.has(caseName));

        usedCaseNames.add(caseName);
        const newCase = generateCase({
          name: caseName,
          groupID: multipleCaseRequest.groupID,
        });
        const caseRequest = generateCaseRequest({
          ...newCase,
          points: 0,
          autoPoints: false,
        });
        commit('addCase', caseRequest);
      }
    },
    setLines({ getters }, lines: CaseLine[]) {
      const selectedCase: Case = getters.getSelectedCase;
      selectedCase.lines = lines;
    },
    addNewLine({ getters }) {
      const selectedCase: Case = getters.getSelectedCase;
      const newLine: CaseLine = {
        lineID: uuid(),
        label: 'NEW',
        data: {
          kind: 'line',
          value: '',
        },
      };
      selectedCase.lines.push(newLine);
    },
    updateLine({ getters }, newLine: CaseLine) {
      const selectedCase: Case = getters.getSelectedCase;
      const lineToUpdate = selectedCase.lines.find(
        (line) => line.lineID === newLine.lineID,
      );
      if (lineToUpdate === undefined) {
        return;
      }
      lineToUpdate.label = newLine.label;
      lineToUpdate.data = newLine.data;
    },
    deleteLine({ getters }, lineIDToBeDeleted: LineID) {
      const selectedCase: Case = getters.getSelectedCase;
      selectedCase.lines = selectedCase.lines.filter(
        (line) => line.lineID !== lineIDToBeDeleted,
      );
    },
  },
  getters: {
    getCasesFromGroup: (state) => (groupID: GroupID) => {
      return (
        state.groups.find((group) => group.groupID === groupID)?.cases ?? null
      );
    },
    getAllGroups: (state) => {
      return state.groups;
    },
    getGroupIdsAndNames: (state) => {
      // We use reduce because we don't want to show the ungrouped cases/groups
      // Also this way we avoid chaining a map and a filter
      return state.groups.reduce<{ value: string; text: string }[]>(
        (groups, currGroup) => {
          if (!currGroup.ungroupedCase) {
            return [
              ...groups,
              { value: currGroup.groupID, text: currGroup.name },
            ];
          }
          return [...groups];
        },
        [],
      );
    },
    getAllCases: (state) => {
      return state.groups.reduce((cases: Case[], currCase) => {
        return [...cases, ...currCase.cases];
      }, []);
    },
    getSelectedCase: (state) => {
      const selectedGroup = state.groups.find(
        (group) => group.groupID === state.selected.groupID,
      );
      if (selectedGroup === undefined) {
        return null;
      }
      return (
        selectedGroup.cases.find(
          (_case) => _case.caseID === state.selected.caseID,
        ) ?? null
      );
    },
    getStringifiedLinesFromCaseGroupID: (state) => (
      caseGroupID: CaseGroupID,
    ) => {
      const groupID: GroupID = caseGroupID.groupID;
      const caseID: CaseID = caseGroupID.caseID;
      const _group = state.groups.find((group) => group.groupID === groupID);
      if (_group === undefined) {
        return '';
      }
      const _case = _group.cases.find((thisCase) => thisCase.caseID === caseID);
      if (_group === undefined) {
        return '';
      }
      if (_case == undefined) {
        return '';
      }
      const stringifiedLine: string = _case.lines
        .map((line) => line.data.value)
        .join('\n');
      return stringifiedLine;
    },
    getSelectedGroup: (state) => {
      return (
        state.groups.find(
          (group) => group.groupID === state.selected.groupID,
        ) ?? null
      );
    },
    getUngroupedCases: (state) => {
      return state.groups.filter((group) => group.ungroupedCase === true);
    },
    getGroupsButUngroupedCases: (state) => {
      return state.groups.filter((group) => group.ungroupedCase === false);
    },
    getTotalPointsForUngroupedCases: (state) => {
      return state.groups
        .filter((group) => group.ungroupedCase === true)
        .reduce((accumulator, group) => accumulator + (group.points || 0), 0);
    },
  },
};

export function assignMissingPoints(state: CasesState): CasesState {
  let maxPoints = 100;
  let notDefinedCount = 0;

  for (const group of state.groups) {
    if (!group.autoPoints) {
      maxPoints -= group?.points ?? 0;
    } else {
      notDefinedCount++;
    }
  }

  const individualPoints =
    notDefinedCount && Math.max(maxPoints, 0) / notDefinedCount;

  state.groups = state.groups.map((element) => {
    if (element.autoPoints) {
      element.points = individualPoints;
    }
    return element;
  });

  return state;
}

export function generateCase(
  caseParams: Partial<Case> & { name: string },
): Case {
  return {
    caseID: uuid(),
    groupID: UUID_NIL,
    lines: [],
    points: null,
    ...caseParams,
  };
}

export function generateCaseRequest(
  caseParams: Partial<CaseRequest> & { name: string },
): CaseRequest {
  return {
    caseID: uuid(),
    groupID: UUID_NIL,
    points: 0,
    autoPoints: false,
    lines: [],
    ...caseParams,
  };
}
export function generateGroup(
  groupParams: Partial<Group> & { name: string },
): Group {
  return {
    groupID: uuid(),
    cases: [],
    points: 0,
    autoPoints: false,
    ungroupedCase: false,
    ...groupParams,
  };
}
