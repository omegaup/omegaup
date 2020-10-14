import { shallowMount } from '@vue/test-utils';
import expect from 'expect';
import T from '../../lang';

import collection_problem from './CollectionProblem.vue';

describe('CollectionProblem.vue', () => {
  it('Should display collection', async () => {
    const wrapper = shallowMount(collection_problem, {});

    expect(wrapper.text()).toContain(T.wordsProblems);
  });
});
