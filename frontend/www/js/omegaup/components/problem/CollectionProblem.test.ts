import { shallowMount } from '@vue/test-utils';

import collection_problem from './CollectionProblem.vue';

describe('CollectionProblem.vue', () => {
  it('Should display collection', async () => {
    const title = 'Nivel Básico: Introducción a la programación';
    const wrapper = shallowMount(collection_problem, {
      propsData: {
        title: title,
      },
    });

    expect(wrapper.find('h6').text()).toBe(title);
  });
});
