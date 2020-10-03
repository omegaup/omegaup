import { mount } from '@vue/test-utils';
import expect from 'expect';

import { types } from '../../api_types';
import T from '../../lang';

import problem_Solution from './Solution.vue';

describe('Solution.vue', () => {
  it('Should handle an empty/locked solution', () => {
    const wrapper = mount(problem_Solution, {
      propsData: {
        solution: <types.ProblemStatement | null>null,
        status: 'locked',
        availableTokens: 0,
        allTokens: 0,
      },
    });

    expect(wrapper.text()).toContain(T.solutionLocked.split('<br/>')[0]);
  });

  it('Should handle an empty/unlocked solution', () => {
    const wrapper = mount(problem_Solution, {
      propsData: {
        solution: <types.ProblemStatement | null>null,
        status: 'unlocked',
        availableTokens: 0,
        allTokens: 0,
      },
    });

    expect(wrapper.text()).toContain(T.solutionConfirm.split('<br/>')[0]);
  });

  it('Should handle a non-empty, unlocked solution', () => {
    const wrapper = mount(problem_Solution, {
      propsData: {
        solution: <types.ProblemStatement | null>{
          markdown: 'Hello, World!',
          images: {},
        },
        status: 'unlocked',
        availableTokens: 0,
        allTokens: 0,
      },
    });

    expect(wrapper.text()).toContain('Hello, World!');
  });
});
