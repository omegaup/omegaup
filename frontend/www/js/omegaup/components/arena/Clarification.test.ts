import { shallowMount } from '@vue/test-utils';

import T from '../../lang';

import arena_Clarification from './Clarification.vue';

describe('Clarification.vue', () => {
  const clarification = {
    answer: null,
    author: 'omegaUp',
    clarification_id: 1,
    message: 'Clarificación de prueba 1',
    assignment_alias: 'Tarea de prueba',
    problem_alias: 'Problema de prueba',
    public: false,
    receiver: null,
    time: new Date(),
  };

  it('Should handle contest clarification', async () => {
    const wrapper = shallowMount(arena_Clarification, {
      propsData: {
        clarification,
        inContest: true,
        isAdmin: true,
      },
    });

    expect(wrapper.text()).toContain(clarification.problem_alias);
    expect(wrapper.find('label.form-check-label').text()).toBe(T.wordsPublic);
  });

  it('Should handle course clarification', async () => {
    const wrapper = shallowMount(arena_Clarification, {
      propsData: {
        clarification,
        inContest: false,
        inCourse: true,
        isAdmin: true,
      },
    });

    expect(wrapper.text()).toContain(clarification.assignment_alias);
    expect(wrapper.find('label.form-check-label').text()).toBe(T.wordsPublic);
  });
});
