import { shallowMount } from '@vue/test-utils';

import T from '../../lang';

import contest_AddProblem from './AddProblem.vue';

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
