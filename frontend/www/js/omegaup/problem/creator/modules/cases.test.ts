import store from '../store';
import { NIL as UUID_NIL, v4 as uuid } from 'uuid';
import { generateCase, generateGroup } from '../modules/cases';
import { CaseLine, CaseRequest, MultipleCaseAddRequest } from '../types';

describe('cases.ts', () => {
  beforeEach(() => {
    store.commit('casesStore/resetStore');
  });

  it('Should save an ungrouped case to the store. Should create a ungrouped group with one case inside it', () => {
    const newCase = generateCase({ name: 'case1' }); // casesStore
    const newCaseRequest: CaseRequest = {
      ...newCase,
      points: 0,
      autoPoints: true,
    };
    store.commit('casesStore/addCase', newCaseRequest);
    newCase.points = 100;
    const groupID = store.state.casesStore.groups[0].groupID;
    const ungroupedGroup = generateGroup({
      name: newCase.name,
      groupID: store.state.casesStore.groups[0].groupID,
      points: 100,
      autoPoints: true,
      ungroupedCase: true,
      cases: [{ ...newCase, groupID }],
    });
    expect(store.state.casesStore.groups[0]).toEqual(ungroupedGroup);
    expect(store.state.casesStore.groups[0].cases[0].name).toBe(newCase.name);
    expect(store.state.casesStore.groups[0].cases[0].caseID).toBe(
      newCase.caseID,
    );
  });

  it('Should save a group to the store', () => {
    const newGroup = generateGroup({ name: 'group1' });
    store.commit('casesStore/addGroup', newGroup);
    expect(store.state.casesStore.groups[0]).toBe(newGroup);
  });

  it('Should add a case to a group', () => {
    const newGroup = generateGroup({ name: 'group1' });
    store.commit('casesStore/addGroup', newGroup);
    const newCase = generateCase({ name: 'case1', groupID: newGroup.groupID }); // casesStore
    const newCaseRequest: CaseRequest = {
      ...newCase,
      autoPoints: true,
    };
    store.commit('casesStore/addCase', newCaseRequest);
    expect(store.state.casesStore.groups[0]).toBe(newGroup);
    expect(store.state.casesStore.groups[0].cases[0]).toEqual(newCase);
  });

  it('Should edit an ungrouped case. Should edit both the case and ungrouped group', () => {
    const newCase = generateCase({ name: 'case1' }); // casesStore
    const newCaseRequest: CaseRequest = {
      ...newCase,
      points: 0,
      autoPoints: true,
    };
    store.commit('casesStore/addCase', newCaseRequest);
    expect(store.state.casesStore.groups[0].points).toBe(100);
    expect(store.state.casesStore.groups[0].autoPoints).toBe(true);
    expect(store.state.casesStore.groups[0].cases[0].name).toBe('case1');
    const groupID = store.state.casesStore.groups[0].groupID;
    const editedCaseRequest: CaseRequest = {
      ...newCase,
      groupID,
      name: 'case2',
      points: 50,
      autoPoints: false,
    };
    store.commit('casesStore/editCase', {
      oldGroupID: groupID,
      editedCase: editedCaseRequest,
    });
    expect(store.state.casesStore.groups[0].name).toBe('case2');
    expect(store.state.casesStore.groups[0].points).toBe(50);
    expect(store.state.casesStore.groups[0].autoPoints).toBe(false);
    expect(store.state.casesStore.groups[0].cases[0].name).toBe('case2');
  });
  it('Should edit a case', () => {
    const newGroup = generateGroup({ name: 'group1' });
    store.commit('casesStore/addGroup', newGroup);
    const newCase = generateCase({ name: 'case1', groupID: newGroup.groupID });
    const newCaseRequest: CaseRequest = {
      ...newCase,
      autoPoints: true,
    };
    store.commit('casesStore/addCase', newCaseRequest);
    expect(store.state.casesStore.groups[0]).toEqual({
      ...newGroup,
      cases: [newCase],
    });
    expect(store.state.casesStore.groups[0].cases[0].name).toBe('case1');
    const groupID = store.state.casesStore.groups[0].groupID;
    const editedCaseRequest: CaseRequest = {
      ...newCase,
      groupID,
      name: 'case2',
      points: 0,
      autoPoints: true,
    };
    store.commit('casesStore/editCase', {
      oldGroupID: groupID,
      editedCase: editedCaseRequest,
    });
    expect(store.state.casesStore.groups[0].name).toBe('group1');
    expect(store.state.casesStore.groups[0].cases[0].name).toBe('case2');
  });
  it('Should change an ungrouped case to another group. Should delete the ungrouped group', () => {
    const newCase = generateCase({ name: 'case1' });
    const newCaseRequest: CaseRequest = {
      ...newCase,
      points: 0,
      autoPoints: true,
    };
    store.commit('casesStore/addCase', newCaseRequest);
    const groupID = store.state.casesStore.groups[0].groupID;
    const newGroup = generateGroup({ name: 'group1' });
    store.commit('casesStore/addGroup', newGroup);
    const editedCase: CaseRequest = {
      ...newCaseRequest,
      groupID: newGroup.groupID,
    };
    store.commit('casesStore/editCase', {
      oldGroupID: groupID,
      editedCase: editedCase,
    });
    expect(store.state.casesStore.groups[0]).toEqual({
      ...newGroup,
      cases: [{ ...newCase, groupID: newGroup.groupID }],
    });
  });
  it('Should change the group of one case to another', () => {
    const newGroup1 = generateGroup({ name: 'group1' });
    store.commit('casesStore/addGroup', newGroup1);
    const newGroup2 = generateGroup({ name: 'group2' });
    store.commit('casesStore/addGroup', newGroup2);
    const newCase = generateCase({ name: 'case1', groupID: newGroup1.groupID });
    const newCaseRequest: CaseRequest = {
      ...newCase,
      points: 0,
      autoPoints: true,
    };
    const oldGroupID = store.state.casesStore.groups[0].groupID;
    const newGroupID = store.state.casesStore.groups[1].groupID;
    store.commit('casesStore/addCase', newCaseRequest);
    store.commit('casesStore/editCase', {
      oldGroupID: oldGroupID,
      editedCase: { ...newCaseRequest, groupID: newGroupID },
    });
    expect(store.state.casesStore.groups[0].cases.length).toBe(0);
    expect(store.state.casesStore.groups[1].cases.length).toBe(1);
    expect(store.state.casesStore.groups[1].cases[0]).toEqual({
      ...newCase,
      groupID: newGroupID,
    });
  });
  it('Should remove an ungrouped case', () => {
    const newCase = generateCase({ name: 'case1' });
    const newCaseRequest: CaseRequest = {
      ...newCase,
      points: 0,
      autoPoints: true,
    };
    store.commit('casesStore/addCase', newCaseRequest);
    const groupID = store.state.casesStore.groups[0].groupID;
    store.commit('casesStore/deleteCase', { caseID: newCase.caseID, groupID });
    expect(store.state.casesStore.groups.length).toBe(0);
  });
  it('Should remove a case', () => {
    const newGroup1 = generateGroup({ name: 'group1' });
    store.commit('casesStore/addGroup', newGroup1);
    const newCase = generateCase({ name: 'case1', groupID: newGroup1.groupID });
    const newCaseRequest: CaseRequest = {
      ...newCase,
      points: 0,
      autoPoints: true,
    };
    store.commit('casesStore/addCase', newCaseRequest);
    store.commit('casesStore/deleteCase', {
      groupID: newGroup1.groupID,
      caseID: newCase.caseID,
    });
    expect(store.state.casesStore.groups[0].cases.length).toBe(0);
  });
  it('Should edit a group', () => {
    const newGroup = generateGroup({ name: 'group1' });
    const editedGroup = generateGroup({
      name: 'group2',
      points: 20,
      autoPoints: false,
      groupID: newGroup.groupID,
    });
    store.commit('casesStore/addGroup', newGroup);
    store.commit('casesStore/editGroup', editedGroup);
    expect(store.state.casesStore.groups[0]).toEqual(editedGroup);
  });

  it('Should remove a group', () => {
    const newGroup = generateGroup({ name: 'group1' });
    store.commit('casesStore/addGroup', newGroup);
    store.commit('casesStore/deleteGroup', newGroup.groupID);
    expect(store.state.casesStore.groups[1]).toBeUndefined();
  });
  it('Should calculate the group points individually', () => {
    const newCase1 = generateCase({ name: 'case1' });
    const newCaseRequest1: CaseRequest = {
      ...newCase1,
      points: 0,
      autoPoints: true,
    };
    store.commit('casesStore/addCase', newCaseRequest1);
    expect(store.state.casesStore.groups[0].points).toBe(100);
    const newCase2 = generateCase({ name: 'case2' });
    const newCaseRequest2: CaseRequest = {
      ...newCase2,
      points: 20,
      autoPoints: false,
    };
    store.commit('casesStore/addCase', newCaseRequest2);
    expect(store.state.casesStore.groups[0].points).toBe(100);
    expect(store.state.casesStore.groups[1].points).toBe(20);
  });
  it('Should modify the points if autoPoints is true', () => {
    const newCase1 = generateCase({
      name: 'case1',
    });
    const newCaseRequest1: CaseRequest = {
      ...newCase1,
      points: 50,
      autoPoints: true,
    };
    store.commit('casesStore/addCase', newCaseRequest1);
    const newCase2 = generateCase({
      name: 'case2',
    });
    const newCaseRequest2: CaseRequest = {
      ...newCase2,
      points: 50,
      autoPoints: false,
    };
    store.commit('casesStore/addCase', newCaseRequest2);
    expect(store.state.casesStore.groups[0].points).toBe(100);
    expect(store.state.casesStore.groups[1].points).toBe(50);
  });
  it('Should not assign negative points', () => {
    const newGroup = generateGroup({
      name: 'group1',
      points: 100,
      autoPoints: true,
    });
    store.commit('casesStore/addGroup', newGroup);
    const newCase1 = generateCase({
      name: 'case1',
    });
    const newCaseRequest1: CaseRequest = {
      ...newCase1,
      groupID: newGroup.groupID,
      points: 50,
      autoPoints: false,
    };
    store.commit('casesStore/addCase', newCaseRequest1);
    const newCase2 = generateCase({
      name: 'case2',
    });
    const newCaseRequest2: CaseRequest = {
      ...newCase2,
      groupID: newGroup.groupID,
      points: 70,
      autoPoints: false,
    };
    store.commit('casesStore/addCase', newCaseRequest2);
    const newCase3 = generateCase({
      name: 'case3',
    });
    const newCaseRequest3: CaseRequest = {
      ...newCase3,
      groupID: newGroup.groupID,
      points: 20,
      autoPoints: false,
    };
    store.commit('casesStore/addCase', newCaseRequest3);
    expect(store.state.casesStore.groups[0].points).toBe(140);
    expect(store.state.casesStore.groups[0].cases[0].points).toBe(50);
    expect(store.state.casesStore.groups[0].cases[1].points).toBe(70);
    expect(store.state.casesStore.groups[0].cases[2].points).toBe(20);
  });
  it('Should create multiple ungrouped cases', () => {
    const multipleCaseRequest: MultipleCaseAddRequest = {
      groupID: UUID_NIL,
      numberOfCases: 5,
      prefix: 'case ',
      suffix: '',
    };
    store.dispatch('casesStore/addMultipleCases', multipleCaseRequest);
    expect(store.state.casesStore.groups.length).toBe(5);
    for (let i = 0; i < 5; i++) {
      expect(store.state.casesStore.groups[i].name).toBe(`case ${i + 1}`);
    }
  });
  it('Should create multiple cases inside a group', () => {
    const newGroup = generateGroup({ name: 'group1' });
    store.commit('casesStore/addGroup', newGroup);
    const multipleCaseRequest: MultipleCaseAddRequest = {
      groupID: newGroup.groupID,
      numberOfCases: 5,
      prefix: 'case ',
      suffix: '',
    };
    store.dispatch('casesStore/addMultipleCases', multipleCaseRequest);
    expect(store.state.casesStore.groups.length).toBe(1);
    expect(store.state.casesStore.groups[0].name).toBe(newGroup.name);
    for (let i = 0; i < 5; i++) {
      expect(store.state.casesStore.groups[0].cases[i].name).toBe(
        `case ${i + 1}`,
      );
    }
  });
  it('Should create multiple cases inside a group and skip a used name', () => {
    const newGroup = generateGroup({ name: 'group1' });
    store.commit('casesStore/addGroup', newGroup);
    const newCase = generateCase({
      name: 'case 3',
      groupID: newGroup.groupID,
    });
    const newCaseRequest: CaseRequest = {
      ...newCase,
      points: 0,
      autoPoints: true,
    };
    store.commit('casesStore/addCase', newCaseRequest);
    const multipleCaseRequest: MultipleCaseAddRequest = {
      groupID: newGroup.groupID,
      numberOfCases: 5,
      prefix: 'case ',
      suffix: '',
    };
    store.dispatch('casesStore/addMultipleCases', multipleCaseRequest);
    expect(store.state.casesStore.groups.length).toBe(1);
    expect(store.state.casesStore.groups[0].name).toBe(newGroup.name);
    expect(store.state.casesStore.groups[0].cases[0].name).toBe(`case 3`);
    for (let i = 1; i < 6; i++) {
      if (i >= 3) {
        expect(store.state.casesStore.groups[0].cases[i].name).toBe(
          `case ${i + 1}`,
        );
        continue;
      }
      expect(store.state.casesStore.groups[0].cases[i].name).toBe(`case ${i}`);
    }
  });
  it('Should set all the cases lines to a defined line array', () => {
    const newCase = generateCase({ name: 'case1' });
    const newCaseRequest: CaseRequest = {
      ...newCase,
      points: 0,
      autoPoints: true,
    };
    store.commit('casesStore/addCase', newCaseRequest);
    const groupID = store.state.casesStore.groups[0].groupID;
    store.commit('casesStore/setSelected', {
      groupID: groupID,
      caseID: newCase.caseID,
    });
    const lines: CaseLine[] = [
      {
        lineID: uuid(),
        caseID: newCase.caseID,
        label: 'line1',
        data: { kind: 'line', value: '1' },
      },
      {
        lineID: uuid(),
        caseID: newCase.caseID,
        label: 'line2',
        data: {
          kind: 'array',
          distinct: true,
          max: 10,
          min: 1,
          size: 2,
          value: '1 2',
        },
      },
    ];
    store.dispatch('casesStore/setLines', lines);
    expect(store.state.casesStore.groups[0].cases[0].lines).toEqual(lines);
  });
  it('Should add a new line to a selected case', () => {
    const newCase = generateCase({ name: 'case1' });
    const newCaseRequest: CaseRequest = {
      ...newCase,
      points: 0,
      autoPoints: true,
    };
    store.commit('casesStore/addCase', newCaseRequest);
    const groupID = store.state.casesStore.groups[0].groupID;
    store.commit('casesStore/setSelected', {
      groupID: groupID,
      caseID: newCase.caseID,
    });
    store.dispatch('casesStore/addNewLine');
    const lineID = store.state.casesStore.groups[0].cases[0].lines[0].lineID;
    const lineToBeExpected: CaseLine = {
      lineID: lineID,
      caseID: store.state.casesStore.groups[0].cases[0].caseID,
      label: '',
      data: { kind: 'line', value: '' },
    };
    expect(store.state.casesStore.groups[0].cases[0].lines.length).toBe(1);
    expect(store.state.casesStore.groups[0].cases[0].lines[0]).toEqual(
      lineToBeExpected,
    );
  });
  it('Should update a defined line', () => {
    const newCase = generateCase({ name: 'case1' });
    const newCaseRequest: CaseRequest = {
      ...newCase,
      points: 0,
      autoPoints: true,
    };
    store.commit('casesStore/addCase', newCaseRequest);
    const groupID = store.state.casesStore.groups[0].groupID;
    store.commit('casesStore/setSelected', {
      groupID: groupID,
      caseID: newCase.caseID,
    });
    store.dispatch('casesStore/addNewLine');
    const updatedLine: CaseLine = {
      ...store.state.casesStore.groups[0].cases[0].lines[0],
      label: 'UPDATED',
      data: {
        kind: 'array',
        distinct: true,
        max: 10,
        min: 1,
        size: 2,
        value: '1 2',
      },
    };
    store.dispatch('casesStore/updateLine', updatedLine);
    expect(store.state.casesStore.groups[0].cases[0].lines[0]).toEqual(
      updatedLine,
    );
  });
  it('Should delete a line', () => {
    const newCase = generateCase({ name: 'case1' });
    const newCaseRequest: CaseRequest = {
      ...newCase,
      points: 0,
      autoPoints: true,
    };
    store.commit('casesStore/addCase', newCaseRequest);
    const groupID = store.state.casesStore.groups[0].groupID;
    store.commit('casesStore/setSelected', {
      groupID: groupID,
      caseID: newCase.caseID,
    });
    store.dispatch('casesStore/addNewLine');
    const lineID = store.state.casesStore.groups[0].cases[0].lines[0].lineID;
    store.dispatch('casesStore/deleteLine', lineID);
    expect(store.state.casesStore.groups[0].cases[0].lines.length).toBe(0);
  });

  it('should create and modify a layout', () => {
    const newCase = generateCase({ name: 'case1' });
    const newCaseRequest: CaseRequest = {
      ...newCase,
      points: 0,
      autoPoints: true,
    };
    store.commit('casesStore/addCase', newCaseRequest);

    const groupID = store.state.casesStore.groups[0].groupID;

    store.commit('casesStore/addLayoutFromSelectedCase');
    expect(store.state.casesStore.layouts.length).toBe(0);

    store.commit('casesStore/setSelected', {
      groupID: groupID,
      caseID: newCase.caseID,
    });

    store.commit('casesStore/addLayoutFromSelectedCase');

    const layoutID = store.state.casesStore.layouts[0].layoutID;
    store.commit('casesStore/addNewLineInfoToLayout', layoutID);
    expect(store.state.casesStore.layouts[0].caseLineInfos[0].data.kind).toBe(
      'line',
    );

    const lineInfoID =
      store.state.casesStore.layouts[0].caseLineInfos[0].lineInfoID;
    store.commit('casesStore/editLineInfoKind', [
      layoutID,
      lineInfoID,
      'multiline',
    ]);
    expect(store.state.casesStore.layouts[0].caseLineInfos[0].data.kind).toBe(
      'multiline',
    );

    store.commit('casesStore/editLineInfoKind', [
      layoutID,
      lineInfoID,
      'array',
    ]);
    expect(store.state.casesStore.layouts[0].caseLineInfos[0].data.kind).toBe(
      'array',
    );

    store.commit('casesStore/editLineInfoKind', [
      layoutID,
      lineInfoID,
      'matrix',
    ]);
    expect(store.state.casesStore.layouts[0].caseLineInfos[0].data.kind).toBe(
      'matrix',
    );

    store.commit('casesStore/editLineInfoKind', [layoutID, lineInfoID, 'line']);
    expect(store.state.casesStore.layouts[0].caseLineInfos[0].data.kind).toBe(
      'line',
    );

    store.commit('casesStore/removeLineInfoFromLayout', [layoutID, lineInfoID]);
  });
});
