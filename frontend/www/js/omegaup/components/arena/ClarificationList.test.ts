import { mount } from '@vue/test-utils';
import { types } from '../../api_types';

import T from '../../lang';

import arena_ClarificationList from './ClarificationList.vue';

describe('ClarificationList.vue', () => {
  const clarifications: types.Clarification[] = [
    {
      answer: undefined,
      assignment_alias: 'Tarea de prueba',
      author: 'omegaUp',
      author_classname: 'user-rank-unranked',
      clarification_id: 1,
      message: 'Clarificación de prueba 1',
      problem_alias: 'Problema de prueba',
      public: true,
      receiver: undefined,
      receiver_classname: undefined,
      time: new Date(),
    },
    {
      answer: 'Ok',
      assignment_alias: 'Tarea de prueba',
      author: 'omegaUp',
      author_classname: 'user-rank-specialist',
      clarification_id: 2,
      message: 'Clarificación de prueba 2',
      problem_alias: 'Problema de prueba',
      public: false,
      receiver: undefined,
      receiver_classname: undefined,
      time: new Date(),
    },
  ];

  it('Should handle contest clarifications', async () => {
    const wrapper = mount(arena_ClarificationList, {
      propsData: {
        clarifications,
      },
    });
    expect(wrapper.text()).toContain(T.clarificationProblem);
  });

  it('Should handle course clarifications', async () => {
    const wrapper = mount(arena_ClarificationList, {
      propsData: {
        clarifications,
      },
    });
    expect(wrapper.text()).toContain(clarifications[0].assignment_alias);
  });

  it('Should handle empty clarifications', async () => {
    const wrapper = mount(arena_ClarificationList, {
      propsData: {
        clarifications: [],
      },
    });
    expect(wrapper.text()).toContain(T.clarificationsEmpty);
  });
});
