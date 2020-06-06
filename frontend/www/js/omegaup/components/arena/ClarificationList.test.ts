import { shallowMount } from '@vue/test-utils';
import expect from 'expect';
import Vue from 'vue';

import T from '../../lang';
import { omegaup } from '../../omegaup';

import arena_ClarificationList from './ClarificationList.vue';

describe('ClarificationList.vue', () => {
  const clarifications = [
    {
      answer: null,
      author: 'omegaUp',
      clarification_id: 1,
      contest_alias: 'Concurso de prueba',
      message: 'Clarificación de prueba 1',
      problem_alias: 'Problema de prueba',
      public: true,
      receiver: null,
      time: new Date(),
    },
    {
      answer: 'Ok',
      author: 'omegaUp',
      clarification_id: 2,
      contest_alias: null,
      message: 'Clarificación de prueba 2',
      problem_alias: 'Problema de prueba',
      public: false,
      receiver: null,
      time: new Date(),
    },
  ];
  it('Should differentiate between problem and contest clarifications', async () => {
    const wrapper = shallowMount(arena_ClarificationList, {
      propsData: {
        inContest: false,
        clarifications,
      },
    });
    expect(wrapper.text()).toContain(T.wordsProblem);
    expect(wrapper.text()).not.toContain(T.wordsContest);

    const wrapperTwo = shallowMount(arena_ClarificationList, {
      propsData: {
        inContest: true,
        clarifications,
      },
    });
    expect(wrapperTwo.text()).toContain(T.wordsContest);
  });
});
