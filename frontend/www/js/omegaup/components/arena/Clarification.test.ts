import { shallowMount } from '@vue/test-utils';

import T from '../../lang';

import arena_Clarification from './Clarification.vue';
import * as ui from '../../ui';

describe('Clarification.vue', () => {
  const clarification = {
    answer: null,
    author: 'omegaUp',
    author_classname: 'user-rank-unranked',
    clarification_id: 1,
    message: 'Clarificación de prueba 1',
    assignment_alias: 'Tarea de prueba',
    problem_alias: 'Problema de prueba',
    public: false,
    receiver: null,
    receiver_classname: null,
    time: new Date(),
  };

  it('Should handle contest clarification', async () => {
    const wrapper = shallowMount(arena_Clarification, {
      propsData: {
        clarification,
        isAdmin: true,
      },
    });

    expect(wrapper.text()).toContain(clarification.problem_alias);
    expect(wrapper.find('label.form-check-label').text()).toBe(T.wordsPublic);
  });

  it('Should handle contest clarification on behalf', async () => {
    const wrapper = shallowMount(arena_Clarification, {
      propsData: {
        clarification: {
          ...clarification,
          ...{
            receiver: 'user',
            receiver_classname: 'user-rank-beginner',
          },
        },
        isAdmin: true,
      },
    });

    const formattedString = ui.formatString(T.clarificationsOnBehalf, {
      author: 'omegaUp',
      receiver: 'user',
    });
    expect(wrapper.find('span[data-author]').text()).toContain(formattedString);
  });

  it('Should handle course clarification', async () => {
    const wrapper = shallowMount(arena_Clarification, {
      propsData: {
        clarification,
        isAdmin: true,
      },
    });

    expect(wrapper.text()).toContain(clarification.assignment_alias);
    expect(wrapper.find('label.form-check-label').text()).toBe(T.wordsPublic);
  });
});
