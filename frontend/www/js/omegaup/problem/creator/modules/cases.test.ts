import store from '@/js/omegaup/problem/creator/store';
import { NIL, v4 } from 'uuid';
import { Case, Group } from '../types';

describe('cases.ts', () => {
  it('Should save a case to the store', async () => {
    const casePayload = generateCasePayload('case1'); // casesStore
    store.commit('casesStore/addCase', casePayload);
    expect(store.state.casesStore.groups[0].cases[0]).toBe(casePayload);
  });

  it('Should save a group to the store', async () => {
    const groupPayload = generateGroupPayload('group1');
    store.commit('casesStore/addGroup', groupPayload);
    expect(store.state.casesStore.groups[1]).toBe(groupPayload);
  });

  it('Should calculate case points based on how many cases are in the store', async () => {
    store.commit('casesStore/resetStore');
    const casePayload1 = generateCasePayload('case1');
    store.commit('casesStore/addCase', casePayload1);
    expect(store.state.casesStore.groups[0].cases[0].points).toBe(100);
    const casePayload2 = generateCasePayload('case2');
    store.commit('casesStore/addCase', casePayload2);
    expect(store.state.casesStore.groups[0].cases[0].points).toBe(50);
    expect(store.state.casesStore.groups[0].cases[1].points).toBe(50);
  });

  it('Should edit a case', async () => {
    store.commit('casesStore/resetStore');
    const casePayload = generateCasePayload('case1', 100, true, NIL);
    const editedPayload = generateCasePayload(
      'case2',
      30,
      true,
      NIL,
      casePayload.caseId,
    );
    store.commit('casesStore/addCase', casePayload);
    store.commit('casesStore/editCase', {
      oldGroup: NIL,
      editedCase: editedPayload,
    });
    expect(store.state.casesStore.groups[0].cases[0]).toStrictEqual(
      editedPayload,
    );
  });

  it('Should edit a group', async () => {
    store.commit('casesStore/resetStore');
    const groupPayload = generateGroupPayload('group1');
    const editedGroupPayload = generateGroupPayload(
      'group2',
      20,
      true,
      groupPayload.groupId,
    );
    store.commit('casesStore/addGroup', groupPayload);
    store.commit('casesStore/editGroup', editedGroupPayload);
    expect(store.state.casesStore.groups[1]).toStrictEqual(editedGroupPayload);
  });
  it('Should change a case from one group to another group', async () => {
    store.commit('casesStore/resetStore');
    const groupPayload = generateGroupPayload('group1');
    const casePayload = generateCasePayload('case1');
    const editedCasePayload = { ...casePayload, groupId: groupPayload.groupId };
    store.commit('casesStore/addGroup', groupPayload);
    store.commit('casesStore/addCase', casePayload);
    store.commit('casesStore/editCase', {
      oldGroup: NIL,
      editedCase: editedCasePayload,
    });
    expect(store.state.casesStore.groups[1].cases[0]).toStrictEqual(
      editedCasePayload,
    );
  });
  it('Should remove a group', async () => {
    store.commit('casesStore/resetStore');
    const groupPayload = generateGroupPayload('group1');
    store.commit('casesStore/addGroup', groupPayload);
    store.commit('casesStore/deleteGroup', groupPayload.groupId);
    expect(store.state.casesStore.groups[1]).toBeUndefined();
  });
  it('Should remove a case', async () => {
    store.commit('casesStore/resetStore');
    const casePayload = generateCasePayload('case1');
    store.commit('casesStore/addCase', casePayload);
    store.commit('casesStore/deleteCase', {
      caseId: casePayload.caseId,
      groupId: casePayload.groupId,
    });
    expect(store.state.casesStore.groups[0].cases[0]).toBeUndefined();
  });
});

const generateCasePayload: (
  name: string,
  points?: number,
  defined?: boolean,
  groupId?: string,
  caseId?: string,
) => Case = (
  name,
  points = 0,
  defined = false,
  groupId = NIL,
  caseId = v4(),
) => {
  return {
    caseId: caseId,
    groupId: groupId,
    defined: defined,
    name: name,
    points: points,
    lines: [],
  };
};

const generateGroupPayload: (
  name: string,
  points?: number,
  defined?: boolean,
  groupId?: string,
) => Group = (name, points = 0, defined = false, groupId = v4()) => {
  return {
    groupId: groupId,
    name: name,
    points: points,
    defined: defined,
    cases: [],
  };
};
