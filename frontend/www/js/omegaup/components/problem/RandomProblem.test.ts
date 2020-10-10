import { shallowMount } from '@vue/test-utils';
import expect from 'expect';
import Vue from 'vue';

import T from '../../lang';
import { omegaup } from '../../omegaup';

import problem_random_problem from './RandomProblem.vue';

describe('RandomProblem.vue', () => {
  it('Should handle empty list of problems', async () => {
    const wrapper = shallowMount(problem_random_problem, {});

  });
});