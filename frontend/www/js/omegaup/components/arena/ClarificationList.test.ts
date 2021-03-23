import { shallowMount } from '@vue/test-utils';
import { types } from '../../api_types';

import T from '../../lang';

import arena_ClarificationList from './ClarificationList.vue';

describe('ClarificationList.vue', () => {
  const clarifications: types.ContestClarification[] = [
    {
      answer: undefined,
      author: 'omegaUp',
      clarification_id: 1,
      message: 'Clarificación de prueba 1',
      problem_alias: 'Problema de prueba',
      public: true,
      receiver: undefined,
      time: new Date(),
    },
    {
      answer: 'Ok',
      author: 'omegaUp',
      clarification_id: 2,
      message: 'Clarificación de prueba 2',
      problem_alias: 'Problema de prueba',
      public: false,
      receiver: undefined,
      time: new Date(),
    },
  ];

  it('Should handle contest clarifications', async () => {
    const wrapper = shallowMount(arena_ClarificationList, {
      propsData: {
        inContest: true,
        clarifications,
      },
    });
    expect(wrapper.find('th').text()).toBe(T.wordsProblem);
  });
});
