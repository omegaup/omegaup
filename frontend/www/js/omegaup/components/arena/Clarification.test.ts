import { shallowMount } from '@vue/test-utils';

import T from '../../lang';

import arena_Clarification from './Clarification.vue';
import * as ui from '../../ui';

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
        isAdmin: true,
      },
    });

    expect(wrapper.text()).toContain(clarification.problem_alias);
    expect(wrapper.find('label.form-check-label').text()).toBe(T.wordsPublic);
  });

  it('Should handle contest clarification on behalf', async () => {
    const wrapper = shallowMount(arena_Clarification, {
      propsData: {
        clarification: { ...clarification, ...{ receiver: 'user' } },
        isAdmin: true,
      },
    });

    expect(wrapper.find('span[data-author]').text()).toContain(
      ui.formatString(T.clarificationsOnBehalf, {
        author: 'omegaUp',
        receiver: 'user',
      }),
    );
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
