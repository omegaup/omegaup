import { shallowMount } from '@vue/test-utils';
import expect from 'expect';

import T from '../../lang';

import contest_AddProblem from './AddProblemv2.vue';

describe('AddProblem.vue', () => {
  it('Should handle empty props', async () => {
    const wrapper = shallowMount(contest_AddProblem, {
      propsData: {
        contestAlias: 'testContestAlias',
        initialPoints: 100,
        initialProblems: [],
      },
    });

    expect(wrapper.text()).toContain(T.wordsProblem);
  });
});
