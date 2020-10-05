import { shallowMount } from '@vue/test-utils';
import expect from 'expect';

import T from '../../lang';

import problem_collection from './Collection.vue';

describe('Collection.vue', () => {
  it('Should handle empty list of problems', async () => {
    const wrapper = shallowMount(problem_collection, {});

    expect(wrapper.text()).toContain(T.collectionTitle);
  });
});
