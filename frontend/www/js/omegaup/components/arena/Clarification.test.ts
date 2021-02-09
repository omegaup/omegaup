import { shallowMount } from '@vue/test-utils';

import T from '../../lang';

import arena_Clarification from './Clarification.vue';

describe('Clarification.vue', () => {
  const clarification = {
    answer: null,
    author: 'omegaUp',
    clarification_id: 1,
    contest_alias: 'Concurso de prueba',
    message: 'Clarificación de prueba 1',
    problem_alias: 'Problema de prueba',
    public: false,
    receiver: null,
    time: new Date(),
  };

  it('Should handle clarification', async () => {
    const wrapper = shallowMount(arena_Clarification, {
      propsData: {
        clarification,
        inContest: true,
      },
    });

    expect(wrapper.find('label.form-check-label').text()).toBe(T.wordsPublic);
  });
});
