import { shallowMount } from '@vue/test-utils';
import expect from 'expect';
import Vue from 'vue';

import T from '../../lang';
import { omegaup } from '../../omegaup';

import problem_collection from './Collection.vue';

describe('Collection.vue', () => {
  it('Should handle empty list of problems', async () => {
    const wrapper = shallowMount(problem_collection, {});

    expect(wrapper.text()).toContain(T.collectionTitle);
    expect(wrapper.text()).toContain(T.problemCollectionEducationLevel);
    expect(wrapper.text()).toContain(T.problemCollectionOthers);
  });
});
