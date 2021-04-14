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
  it('Should handle empty props', () => {
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

    expect(wrapper.find('button[data-remove-problem]')).toBeTruthy();
    await wrapper.find('button[data-remove-problem]').trigger('click');
    expect(wrapper.emitted('remove-problem')).toBeDefined();
    expect(wrapper.emitted('remove-problem')).toEqual([['problem']]);
  });

  it('Should disable the delete button when a problem has submissions', () => {
    const wrapper = shallowMount(contest_AddProblem, {
      propsData: {
        contestAlias: 'testContestAlias',
        initialPoints: 100,
        initialProblems: [{ ...problem, has_submissions: true }],
      },
    });

    expect(wrapper.find('button[data-remove-problem-disabaled]')).toBeTruthy();
  });

  it('Should update a problem in the list', async () => {
    const wrapper = shallowMount(contest_AddProblem, {
      propsData: {
        contestAlias: 'testContestAlias',
        initialPoints: 100,
        initialProblems: [
          { ...problem, has_submissions: false },
          { ...problem, alias: 'problem_2', letter: 'B', title: 'Problem 2' },
        ],
      },
    });

    expect(wrapper.find('form').text()).not.toContain(
      T.contestAddproblemChooseVersion,
    );
    expect(wrapper.find('form').text()).not.toContain(T.wordsPoints);
    expect(wrapper.find('form').text()).not.toContain(
      T.contestAddproblemProblemOrder,
    );

    expect(wrapper.find('button[data-update-problem]')).toBeTruthy();
    await wrapper.find('button[data-update-problem]').trigger('click');

    expect(wrapper.find('form').text()).toContain(
      T.contestAddproblemChooseVersion,
    );
    expect(wrapper.find('form').text()).toContain(T.wordsPoints);
    expect(wrapper.find('form').text()).toContain(
      T.contestAddproblemProblemOrder,
    );
    expect(wrapper.find('button.add-problem').text()).toBe(
      T.wordsUpdateProblem,
    );

    const updatedProblem = {
      order: '2',
      points: '98',
    };
    wrapper.setData(updatedProblem);

    await wrapper.find('button.add-problem').trigger('click');
    expect(wrapper.emitted('add-problem')).toBeDefined();
    expect(wrapper.emitted('add-problem')).toEqual([
      [
        {
          isUpdate: true,
          problem: {
            ...updatedProblem,
            alias: 'problem',
            commit: undefined,
          },
        },
      ],
    ]);
  });
});
