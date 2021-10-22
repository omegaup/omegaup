import store from '@/js/omegaup/problem/creator/store';
import { NIL as UUID_NIL, v4 as uuid } from 'uuid';
import { Case, Group } from '../types';

describe('cases.ts', () => {
  beforeEach(() => {
    store.commit('casesStore/resetStore');
  });

  it('Should save a case to the store', () => {
    const newCase = generateCase({ name: 'case1' }); // casesStore
    store.commit('casesStore/addCase', newCase);
    expect(store.state.casesStore.groups[0].cases[0]).toBe(newCase);
  });

  it('Should save a group to the store', () => {
    const newGroup = generateGroup({ name: 'group1' });
    store.commit('casesStore/addGroup', newGroup);
    expect(store.state.casesStore.groups[1]).toBe(newGroup);
  });

  it('Should calculate case points based on how many cases are in the store', () => {
    const newCase1 = generateCase({ name: 'case1' });
    store.commit('casesStore/addCase', newCase1);
    expect(store.state.casesStore.groups[0].cases[0].points).toBe(100);
    const newCase2 = generateCase({ name: 'case2' });
    store.commit('casesStore/addCase', newCase2);
    expect(store.state.casesStore.groups[0].cases[0].points).toBe(50);
    expect(store.state.casesStore.groups[0].cases[1].points).toBe(50);
  });
  it('Should not divide by 0. All cases are defined', () => {
    const newCase1 = generateCase({
      name: 'case1',
      points: 50,
      pointsDefined: true,
    });
    store.commit('casesStore/addCase', newCase1);
    const newCase2 = generateCase({
      name: 'case2',
      points: 50,
      pointsDefined: true,
    });
    store.commit('casesStore/addCase', newCase2);
    expect(store.state.casesStore.groups[0].cases[0].points).toBe(50);
    expect(store.state.casesStore.groups[0].cases[1].points).toBe(50);
  });
  it('Should not assign negative points', () => {
    const newCase1 = generateCase({
      name: 'case1',
      points: 50,
      pointsDefined: true,
    });
    store.commit('casesStore/addCase', newCase1);
    const newCase2 = generateCase({
      name: 'case2',
      points: 70,
      pointsDefined: true,
    });
    store.commit('casesStore/addCase', newCase2);
    const newCase3 = generateCase({
      name: 'case3',
    });
    store.commit('casesStore/addCase', newCase3);
    expect(store.state.casesStore.groups[0].cases[0].points).toBe(50);
    expect(store.state.casesStore.groups[0].cases[1].points).toBe(70);
    expect(store.state.casesStore.groups[0].cases[2].points).toBe(0);
  });

  it('Should edit a case', () => {
    const newCase = generateCase({
      name: 'case1',
      points: 100,
      pointsDefined: true,
      groupID: UUID_NIL,
    });
    const editedCase = generateCase({
      name: 'case2',
      points: 30,
      pointsDefined: true,
      groupID: UUID_NIL,
      caseID: newCase.caseID,
    });
    store.commit('casesStore/addCase', newCase);
    store.commit('casesStore/editCase', {
      oldGroupID: UUID_NIL,
      editedCase: editedCase,
    });
    expect(store.state.casesStore.groups[0].cases[0]).toStrictEqual(editedCase);
  });

  it('Should edit a group', () => {
    const newGroup = generateGroup({ name: 'group1' });
    const editedGroup = generateGroup({
      name: 'group2',
      points: 20,
      pointsDefined: true,
      groupID: newGroup.groupID,
    });
    store.commit('casesStore/addGroup', newGroup);
    store.commit('casesStore/editGroup', editedGroup);
    expect(store.state.casesStore.groups[1]).toStrictEqual(editedGroup);
  });
  it('Should change a case from one group to another group', () => {
    const newGroup = generateGroup({ name: 'group1' });
    const newCase = generateCase({ name: 'case1' });
    const editedCase = { ...newCase, groupID: newGroup.groupID };
    store.commit('casesStore/addGroup', newGroup);
    store.commit('casesStore/addCase', newCase);
    store.commit('casesStore/editCase', {
      oldGroupID: UUID_NIL,
      editedCase: editedCase,
    });
    expect(store.state.casesStore.groups[1].cases[0]).toStrictEqual(editedCase);
  });
  it('Should remove a group', () => {
    const newGroup = generateGroup({ name: 'group1' });
    store.commit('casesStore/addGroup', newGroup);
    store.commit('casesStore/deleteGroup', newGroup.groupID);
    expect(store.state.casesStore.groups[1]).toBeUndefined();
  });
  it('Should remove a case', () => {
    store.commit('casesStore/resetStore');
    const newCase = generateCase({ name: 'case1' });
    store.commit('casesStore/addCase', newCase);
    store.commit('casesStore/deleteCase', {
      caseID: newCase.caseID,
      groupID: newCase.groupID,
    });
    expect(store.state.casesStore.groups[0].cases[0]).toBeUndefined();
  });
});

function generateCase(caseParams: Partial<Case> & { name: string }): Case {
  return {
    caseID: uuid(),
    groupID: UUID_NIL,
    pointsDefined: false,
    points: 0,
    lines: [],
    ...caseParams,
  };
}

function generateGroup(groupParams: Partial<Group> & { name: string }): Group {
  return {
    groupID: uuid(),
    cases: [],
    points: 0,
    pointsDefined: false,
    ...groupParams,
  };
}
