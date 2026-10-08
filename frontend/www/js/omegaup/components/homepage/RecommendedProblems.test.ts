import { shallowMount } from '@vue/test-utils';
import Vue from 'vue';
import type { ComponentOptions } from 'vue';

import T from '../../lang';
import type { types } from '../../api_types';

import homepage_RecommendedProblems from './RecommendedProblems.vue';

const RecommendedProblems = homepage_RecommendedProblems as ComponentOptions<Vue>;

const sampleProblems: types.RecommendedProblem[] = [
  {
    alias: 'sumas',
    title: 'Sumas',
    difficulty: 1.5,
    quality: 3.0,
    score: 0.9,
    solved_problem_alias: 'aplusb',
    solved_problem_title: 'A+B',
  },
  {
    alias: 'fibonacci',
    title: 'Fibonacci',
    difficulty: 2.0,
    quality: 3.5,
    score: 0.4,
    solved_problem_alias: 'aplusb',
    solved_problem_title: 'A+B',
  },
];

describe('RecommendedProblems.vue', () => {
  it('Should render nothing when there are no recommendations', () => {
    const wrapper = shallowMount(RecommendedProblems, {
      propsData: { problems: [] },
    });
    expect(wrapper.find('.card').exists()).toBe(false);
  });

  it('Should render one entry per recommended problem', () => {
    const wrapper = shallowMount(RecommendedProblems, {
      propsData: { problems: sampleProblems },
    });
    expect(wrapper.find('.card-header').text()).toBe(
      T.homepageRecommendedProblemsTitle,
    );
    const entries = wrapper.findAll('a.list-group-item');
    expect(entries.length).toBe(2);
    expect(entries.at(0).attributes('href')).toBe('/arena/problem/sumas/');
    expect(entries.at(0).text()).toContain('Sumas');
  });

  it('Should explain which solved problem caused the recommendation', () => {
    const wrapper = shallowMount(RecommendedProblems, {
      propsData: { problems: sampleProblems },
    });
    expect(wrapper.find('small').text()).toContain('A+B');
  });
});
