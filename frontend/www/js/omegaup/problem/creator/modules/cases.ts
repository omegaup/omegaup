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
  Layout,
  CaseLineInfo,
  LayoutID,
  LineInfoID,
  CaseLineKind,
  MatrixDistinctType,
  CaseLineData,
} from '../types';
import T from '../../../lang';
import { Module } from 'vuex';
import { NIL as UUID_NIL, v4 as uuid } from 'uuid';
import Vue from 'vue';
export interface CasesState {
  groups: Group[];
  selected: CaseGroupID;
  layouts: Layout[];
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
    layouts: [],
    hide: false,
  },
  mutations: {
    resetStore(state) {
      state.groups = [];
      state.selected = {
        caseID: UUID_NIL,
        groupID: UUID_NIL,
      };
      state.layouts = [];
      state.hide = false;
    },
    replaceState(state, uploadedState: CasesState) {
      state.groups = uploadedState.groups;
      state.selected = uploadedState.selected;
      state.layouts = uploadedState.layouts;
      state.hide = uploadedState.hide;
    },
    validateAndFixPoints(state) {
      // As we are doing nothing with the state directly, es-lint complains, but this is crucial to fix the points distribution.
      // eslint-disable-next-line
      state = assignMissingPoints(state, true);
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
    updateGroup(
      state,
      [groupID, newName, newPoints]: [GroupID, string, number],
    ) {
      const targetGroup = state.groups.find(
        (_group) => _group.groupID === groupID,
      );
      if (!targetGroup) return;
      targetGroup.name = newName;
      targetGroup.points = newPoints;
    },
    addCase(state, caseRequest: CaseRequest) {
      if (caseRequest.groupID === UUID_NIL) {
        // Should create a new group with the same name
        if (caseRequest.autoPoints) {
          caseRequest.points = 100;
        }
        const newCase = generateCase({
          name: caseRequest.name,
          caseID: caseRequest.caseID,
          autoPoints: caseRequest.autoPoints,
          points: caseRequest.points,
        });
        const groupID = uuid();
        const newGroup = generateGroup({
          name: caseRequest.name,
          groupID: groupID,
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
            autoPoints: caseRequest.autoPoints,
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
    updateCase(state, [oldGroupID, updateCaseRequest]: [GroupID, CaseRequest]) {
      if (
        updateCaseRequest.groupID === UUID_NIL &&
        updateCaseRequest.autoPoints
      ) {
        updateCaseRequest.points = 100;
      }
      const oldGroup = state.groups.find(
        (_group) => _group.groupID === oldGroupID,
      );
      if (!oldGroup) return;
      const caseToEdit = oldGroup.cases.find(
        (_case) => _case.caseID === updateCaseRequest.caseID,
      );
      if (!caseToEdit) return;
      if (updateCaseRequest.groupID === UUID_NIL && oldGroup.ungroupedCase) {
        if (caseToEdit.name !== updateCaseRequest.name) {
          caseToEdit.name = updateCaseRequest.name;
          oldGroup.name = updateCaseRequest.name;
        }
        caseToEdit.points = updateCaseRequest.points;
        oldGroup.points = updateCaseRequest.points;
        caseToEdit.autoPoints = updateCaseRequest.autoPoints;
        oldGroup.autoPoints = updateCaseRequest.autoPoints;
        state = assignMissingPoints(state);
        return;
      }
      if (
        updateCaseRequest.groupID === UUID_NIL &&
        oldGroup.ungroupedCase === false
      ) {
        oldGroup.cases = oldGroup.cases.filter(
          (_case) => _case.caseID !== caseToEdit.caseID,
        );
        const groupID = uuid();
        const newGroup = generateGroup({
          name: updateCaseRequest.name,
          groupID: groupID,
          points: updateCaseRequest.points,
          autoPoints: updateCaseRequest.autoPoints,
          ungroupedCase: true,
          cases: [caseToEdit],
        });
        caseToEdit.name = updateCaseRequest.name;
        caseToEdit.groupID = groupID;
        caseToEdit.points = updateCaseRequest.points;
        state.groups.push(newGroup);
        state = assignMissingPoints(state);
        return;
      }
      caseToEdit.name = updateCaseRequest.name;
      caseToEdit.points = updateCaseRequest.points;

      if (caseToEdit.groupID === updateCaseRequest.groupID) {
        return;
      }
      caseToEdit.groupID = updateCaseRequest.groupID;

      oldGroup.cases = oldGroup.cases.filter(
        (_case) => _case.caseID !== updateCaseRequest.caseID,
      );

      if (oldGroup.ungroupedCase && oldGroup.cases.length === 0) {
        state.groups = state.groups.filter(
          (_group) => _group.groupID !== oldGroup.groupID,
        );
      }
      state.groups
        .find((_group) => _group.groupID === updateCaseRequest.groupID)
        ?.cases.push(caseToEdit);
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
    addLayoutFromSelectedCase(state) {
      if (!state.selected.caseID || !state.selected.groupID) {
        return;
      }
      const selectedGroup = state.groups.find(
        (group) => group.groupID == state.selected.groupID,
      );
      if (!selectedGroup) return;
      const selectedCase = selectedGroup.cases.find(
        (_case) => _case.caseID == state.selected.caseID,
      );
      if (!selectedCase) return;
      const caseLineInfos: CaseLineInfo[] = selectedCase.lines.map(
        (caseLine) => ({
          lineInfoID: uuid(),
          label: caseLine.label,
          data: {
            ...caseLine.data,
            value: '',
          },
        }),
      );
      const layoutFromSelectedCase: Layout = {
        layoutID: uuid(),
        name: selectedGroup.name + '_' + selectedCase.name,
        caseLineInfos: caseLineInfos,
      };
      state.layouts.push(layoutFromSelectedCase);
    },
    editLayoutName(state, [layoutID, newValue]: [LayoutID, string]) {
      const targetLayout = state.layouts.find(
        (layout) => layout.layoutID === layoutID,
      );
      if (!targetLayout) {
        return;
      }
      targetLayout.name = newValue;
    },
    removeLayout(state, layoutIDToBeDeleted: LineID) {
      state.layouts = state.layouts.filter(
        (layout) => layout.layoutID !== layoutIDToBeDeleted,
      );
    },
    addNewLineInfoToLayout(state, layoutID: LayoutID) {
      const targetLayout = state.layouts.find(
        (layout) => layout.layoutID === layoutID,
      );
      if (!targetLayout) return;
      const newLineInfo: CaseLineInfo = {
        lineInfoID: uuid(),
        label: '',
        data: {
          kind: 'line',
          value: '',
        },
      };
      targetLayout.caseLineInfos.push(newLineInfo);
    },
    editLineInfoKind(
      state,
      [layoutID, lineInfoID, _kind]: [LayoutID, LineInfoID, CaseLineKind],
    ) {
      const targetLayout = state.layouts.find(
        (layout) => layout.layoutID === layoutID,
      );
      if (!targetLayout) return;
      const selectedLineInfo = targetLayout.caseLineInfos.find(
        (lineInfo) => lineInfo.lineInfoID === lineInfoID,
      );
      if (!selectedLineInfo) return;
      const defaultMatrixDistinctType: MatrixDistinctType =
        MatrixDistinctType.None;
      const lineInfoData: CaseLineData = (() => {
        switch (_kind) {
          case 'line':
            return {
              kind: _kind,
              value: '',
            };
          case 'multiline':
            return {
              kind: _kind,
              value: '',
            };
          case 'array':
            return {
              kind: _kind,
              size: 10,
              min: 0,
              max: 100,
              distinct: false,
              value: '',
            };
          case 'matrix':
            return {
              kind: _kind,
              rows: 3,
              cols: 3,
              min: 0,
              max: 100,
              distinct: defaultMatrixDistinctType,
              value: '',
            };
        }
      })();
      selectedLineInfo.data = lineInfoData;
    },
    removeLineInfoFromLayout(
      state,
      [layoutID, lineInfoID]: [LayoutID, LineInfoID],
    ) {
      const targetLayout = state.layouts.find(
        (layout) => layout.layoutID === layoutID,
      );
      if (!targetLayout) return;
      targetLayout.caseLineInfos = targetLayout.caseLineInfos.filter(
        (lineInfo) => lineInfo.lineInfoID !== lineInfoID,
      );
    },
    copyLayout(state, layoutID: LayoutID) {
      const targetLayout = state.layouts.find(
        (layout) => layout.layoutID === layoutID,
      );
      if (!targetLayout) return;
      const copiedLineInfos: CaseLineInfo[] = targetLayout.caseLineInfos.map(
        (lineInfo) => ({
          lineInfoID: uuid(),
          label: lineInfo.label,
          data: {
            ...lineInfo.data,
          },
        }),
      );
      const copiedLayout: Layout = {
        layoutID: uuid(),
        name: targetLayout.name + T.problemCreatorLayoutWordCopy,
        caseLineInfos: copiedLineInfos,
      };
      state.layouts.push(copiedLayout);
    },
    addNewLayout(state) {
      const newLayout: Layout = {
        layoutID: uuid(),
        name: T.problemCreatorLayoutNew,
        caseLineInfos: [],
      };
      state.layouts.push(newLayout);
    },
    enforceLayoutToTheSelectedCase(state, layoutID: LayoutID) {
      const targetLayout = state.layouts.find(
        (layout) => layout.layoutID === layoutID,
      );
      if (!targetLayout) return;
      if (!state.selected.caseID || !state.selected.groupID) {
        return;
      }
      const selectedGroup = state.groups.find(
        (group) => group.groupID == state.selected.groupID,
      );
      if (!selectedGroup) return;
      const selectedCase = selectedGroup.cases.find(
        (_case) => _case.caseID == state.selected.caseID,
      );
      if (!selectedCase) return;
      const linesFromLayout: CaseLine[] = targetLayout.caseLineInfos.map(
        (lineInfo) => ({
          lineID: uuid(),
          caseID: selectedCase.caseID,
          label: lineInfo.label,
          data: {
            ...lineInfo.data,
          },
        }),
      );
      selectedCase.lines = linesFromLayout;
    },
    enforceLayoutToAllCases(state, layoutID: LayoutID) {
      state.groups.forEach((_group) => {
        _group.cases.forEach((_case) => {
          const targetLayout = state.layouts.find(
            (layout) => layout.layoutID === layoutID,
          );
          if (!targetLayout) return;
          const linesFromLayout: CaseLine[] = targetLayout.caseLineInfos.map(
            (lineInfo) => ({
              lineID: uuid(),
              caseID: _case.caseID,
              label: lineInfo.label,
              data: {
                ...lineInfo.data,
              },
            }),
          );
          _case.lines = linesFromLayout;
        });
      });
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
    editLineValue({ getters }, [lineID, value]: [LineID, CaseLineKind]) {
      const selectedLine: CaseLine = getters.getLineFromID(lineID);
      if (!selectedLine) return;
      selectedLine.data.value = value;
    },
    editLineKind({ getters }, [lineID, _kind]: [LineID, CaseLineKind]) {
      const selectedLine: CaseLine = getters.getLineFromID(lineID);
      if (!selectedLine) return;
      const defaultMatrixDistinctType: MatrixDistinctType =
        MatrixDistinctType.None;
      const lineData: CaseLineData = (() => {
        switch (_kind) {
          case 'line':
            return {
              kind: _kind,
              value: '',
            };
          case 'multiline':
            return {
              kind: _kind,
              value: '',
            };
          case 'array':
            return {
              kind: _kind,
              size: 10,
              min: 0,
              max: 100,
              distinct: false,
              value: '',
            };
          case 'matrix':
            return {
              kind: _kind,
              rows: 3,
              cols: 3,
              min: 0,
              max: 100,
              distinct: defaultMatrixDistinctType,
              value: '',
            };
        }
      })();
      selectedLine.data = lineData;
    },
    addNewLine({ getters }) {
      const selectedCase: Case = getters.getSelectedCase;
      const newLine: CaseLine = {
        lineID: uuid(),
        caseID: selectedCase.caseID,
        label: '',
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
      if (!lineToUpdate) {
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
    deleteLinesForSelectedCase({ getters }) {
      const selectedCase: Case = getters.getSelectedCase;
      selectedCase.lines = [];
    },
  },
  getters: {
    getCasesFromGroup: (state) => (groupID: GroupID) => {
      return (
        state.groups.find((group) => group.groupID === groupID)?.cases ?? null
      );
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
    getAllLayouts: (state) => {
      return state.layouts;
    },
    getSelectedCase: (state) => {
      const selectedGroup = state.groups.find(
        (group) => group.groupID === state.selected.groupID,
      );
      if (!selectedGroup) {
        return null;
      }
      return (
        selectedGroup.cases.find(
          (_case) => _case.caseID === state.selected.caseID,
        ) ?? null
      );
    },
    getStringifiedLinesFromCaseGroupID:
      (state) => (caseGroupID: CaseGroupID) => {
        const groupID: GroupID = caseGroupID.groupID;
        const caseID: CaseID = caseGroupID.caseID;
        const _group = state.groups.find((group) => group.groupID === groupID);
        if (_group === undefined) {
          return '';
        }
        const _case = _group.cases.find(
          (thisCase) => thisCase.caseID === caseID,
        );
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
    getLineFromID: (state) => (lineID: LineID) => {
      const selectedGroup = state.groups.find(
        (group) => group.groupID === state.selected.groupID,
      );
      if (!selectedGroup) {
        return null;
      }
      const selectedCase = selectedGroup.cases.find(
        (_case) => _case.caseID === state.selected.caseID,
      );
      if (!selectedCase) {
        return null;
      }
      const selectedLine = selectedCase.lines.find(
        (line) => line.lineID === lineID,
      );
      if (!selectedLine) {
        return null;
      }
      return selectedLine;
    },
    getLinesFromSelectedCase: (state) => {
      const selectedGroup = state.groups.find(
        (group) => group.groupID === state.selected.groupID,
      );
      if (!selectedGroup) {
        return [];
      }
      const selectedCase = selectedGroup.cases.find(
        (_case) => _case.caseID === state.selected.caseID,
      );
      if (!selectedCase) {
        return [];
      }
      return selectedCase.lines;
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

export function assignMissingPoints(
  state: CasesState,
  fixPoints = false,
): CasesState {
  // The updated points distribution will have the following structure:
  // Each ungrouped case will have by default 100 points (if autoPoints is true).
  // Each group will have by default 100 points (if autopoints is true).
  // Lets say, we have n cases in the group.
  // a among them have points set by the user.
  // remaining b are having autoPoints as true.
  // So a + b = n.
  // if autopoints of the group is true, it has 100 points.
  // So, each notDefined points will have score (1/b)*(100 - Σp(a)).
  // Now, if the Σp(a) exceeds 100, group point will be set to Σp(a).
  // Note that, in this case, all the notDefined cases will get 0 points.
  // If the autopoint is not true, then the point in the group should have higher preference.
  // In that case:
  // 1. Users need to use all the points in the group.
  // 2. Σp(a) should not exceed the total points of the group.
  // This needs a validator that will let users know if the point distribution is correct.
  // And there will be an option to automatically fix the points.
  // If users fail to satisfy that, validator will keep the casepoints ratio intact but scale them to satisfy the group's total points.

  state.groups = state.groups.map((_group) => {
    const groupAutoPoints = _group.autoPoints;

    let casesPoints = 0;
    let notDefinedCount = 0;

    _group.cases.forEach((_case) => {
      if (_case.autoPoints) {
        notDefinedCount += 1;
      } else {
        casesPoints += _case.points;
      }
    });

    if (groupAutoPoints) {
      if (casesPoints >= _group.points) {
        _group.points = casesPoints;
      } else if (casesPoints < _group.points && notDefinedCount === 0) {
        _group.points = casesPoints;
      } else {
        _group.points = 100;
      }
      if (_group.points === 0 && notDefinedCount != 0) {
        _group.points = 100;
      }
      const remainingAssignablePoints = _group.points - casesPoints;
      _group.cases.forEach((_case) => {
        if (_case.autoPoints) {
          _case.points = remainingAssignablePoints / notDefinedCount;
        }
      });
    }
    if (!groupAutoPoints && !fixPoints) {
      if (notDefinedCount > 0) {
        const remainingAssignablePoints = _group.points - casesPoints;
        _group.cases.forEach((_case) => {
          if (_case.autoPoints) {
            _case.points = remainingAssignablePoints / notDefinedCount;
          }
        });
      }
    }
    if (!groupAutoPoints && fixPoints) {
      if (casesPoints > _group.points) {
        const distributionRatio = _group.points / casesPoints;
        _group.cases.forEach((_case) => {
          _case.points = _case.points * distributionRatio;
        });
      } else {
        if (notDefinedCount !== 0) {
          const remainingAssignablePoints = _group.points - casesPoints;
          _group.cases.forEach((_case) => {
            if (_case.autoPoints) {
              _case.points = remainingAssignablePoints / notDefinedCount;
            }
          });
        } else {
          const distributionRatio = _group.points / casesPoints;
          _group.cases.forEach((_case) => {
            _case.points = _case.points * distributionRatio;
          });
        }
      }
    }
    return _group;
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
    points: 0,
    autoPoints: true,
    output: '',
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
