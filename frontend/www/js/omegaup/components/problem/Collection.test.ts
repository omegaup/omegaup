import { shallowMount } from '@vue/test-utils';
import expect from 'expect';
import Vue from 'vue';

import T from '../../lang';
import { omegaup } from '../../omegaup';

import problem_collections from './Collection.vue';

describe('Collection.vue', () => {
  it('Should handle empty list of problems', async () => {
    const wrapper = shallowMount(problem_collections, {});
  });
});
