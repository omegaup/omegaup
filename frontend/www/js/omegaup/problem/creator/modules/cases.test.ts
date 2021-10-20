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
    const newGroup = generateGroupPayload({ name: 'group1' });
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

  it('Should edit a case', () => {
    const newCase = generateCase({
      name: 'case1',
      points: 100,
      defined: true,
      groupID: UUID_NIL,
    });
    const editedCase = generateCase({
      name: 'case2',
      points: 30,
      defined: true,
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
    const newGroup = generateGroupPayload({ name: 'group1' });
    const editedGroup = generateGroupPayload({
      name: 'group2',
      points: 20,
      defined: true,
      groupID: newGroup.groupID,
    });
    store.commit('casesStore/addGroup', newGroup);
    store.commit('casesStore/editGroup', editedGroup);
    expect(store.state.casesStore.groups[1]).toStrictEqual(editedGroup);
  });
  it('Should change a case from one group to another group', () => {
    const newGroup = generateGroupPayload({ name: 'group1' });
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
    const newGroup = generateGroupPayload({ name: 'group1' });
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

interface generateCaseOptions {
  name: string;
  points?: number;
  defined?: boolean;
  groupID?: string;
  caseID?: string;
}

function generateCase({
  name,
  points = 0,
  defined = false,
  groupID = UUID_NIL,
  caseID = uuid(),
}: generateCaseOptions): Case {
  return {
    caseID: caseID,
    groupID: groupID,
    defined: defined,
    name: name,
    points: points,
    lines: [],
  };
}

interface generateGroupOptions {
  name: string;
  points?: number;
  defined?: boolean;
  groupID?: string;
}

function generateGroupPayload({
  name,
  points = 0,
  defined = false,
  groupID = uuid(),
}: generateGroupOptions): Group {
  return {
    groupID: groupID,
    name: name,
    points: points,
    defined: defined,
    cases: [],
  };
}
