import { shallowMount } from '@vue/test-utils';

import { types } from '../../api_types';
import T from '../../lang';

import contest_AddProblem from './AddProblem.vue';

const problem: types.ProblemsetProblem = {
  accepted: 0,
  accepts_submissions: true,
  alias: 'problem',
  commit: '54318b04024976471c4cac1d2efeb81021d9396b',
  difficulty: 1.0,
  has_submissions: false,
  input_limit: 1024,
  languages: 'c,cpp',
  letter: 'A',
  order: 1,
  points: 100,
  quality_seal: true,
  submissions: 0,
  title: 'Problem',
  version: '54318b04024976471c4cac1d2efeb81021d9396b',
  visibility: 1,
  visits: 0,
};

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

  it('Should enable the delete button when a problem has no submissions', async () => {
    const wrapper = shallowMount(contest_AddProblem, {
      propsData: {
        contestAlias: 'testContestAlias',
        initialPoints: 100,
        initialProblems: [{ ...problem, has_submissions: false }],
      },
    });

    expect(wrapper.find('td[data-remove-problem] button').text()).toContain(
      '×',
    );
  });

  it('Should disable the delete button when a problem has submissions', async () => {
    const wrapper = shallowMount(contest_AddProblem, {
      propsData: {
        contestAlias: 'testContestAlias',
        initialPoints: 100,
        initialProblems: [{ ...problem, has_submissions: true }],
      },
    });

    expect(wrapper.find('td[data-remove-problem] button').text()).toContain(
      '🚫',
    );
  });
});
