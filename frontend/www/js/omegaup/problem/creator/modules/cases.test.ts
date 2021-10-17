import store from '@/js/omegaup/problem/creator/store';
import { NIL, v4 } from 'uuid';
import { Case, Group, MultipleCaseAdd } from '../types';

describe('cases.ts', () => {
  it('Should save a case to the store', async () => {
    const casePayload = generateCasePayload('case1'); // casesStore
    store.commit('casesStore/addCase', casePayload);
    expect(store.state.casesStore.groups[0].cases[0]).toBe(casePayload);
  });
  it('Should save multiple cases to the store', () => {
    store.commit('casesStore/resetStore');
    const multiPayload: MultipleCaseAdd = {
      groupId: NIL,
      number: 3,
      prefix: 'case',
      suffix: '',
    };
    store.dispatch('casesStore/addMultipleCases', multiPayload);
    expect(store.state.casesStore.groups[0].cases.length).toBe(3);
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
