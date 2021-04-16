import { mount, shallowMount } from '@vue/test-utils';

import { types } from '../../api_types';
import T from '../../lang';

import contest_AddProblem from './AddProblem.vue';

const commit = '54318b04024976471c4cac1d2efeb81021d9396b';
const problem: types.ProblemsetProblem = {
  accepted: 0,
  accepts_submissions: true,
  alias: 'problem',
  commit,
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
  version: commit,
  visibility: 1,
  visits: 0,
};
const revision: types.ProblemVersion = {
  author: {
    name: 'Problem Author',
  },
  commit,
  committer: {},
  message: 'Some message',
  parents: [],
  tree: {},
  version: commit,
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

    expect(wrapper.find('button[data-remove-problem="problem"]')).toBeTruthy();
    await wrapper
      .find('button[data-remove-problem="problem"]')
      .trigger('click');
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

    expect(
      wrapper.find('button[data-remove-problem-disabled="problem"]'),
    ).toBeTruthy();
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

    expect(wrapper.find('button[data-update-problem="problem"]')).toBeTruthy();
    await wrapper
      .find('button[data-update-problem="problem"]')
      .trigger('click');

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
            commit,
          },
        },
      ],
    ]);
  });

  it('Should update non-latest version for a problem in the list', async () => {
    const alternativeCommit = 'b6918b04024976471c4cac1d2efeb81021d93345';
    const versionLog: types.ProblemVersion[] = [
      { ...revision },
      { ...revision, commit: alternativeCommit },
    ];

    const wrapper = mount(contest_AddProblem, {
      propsData: {
        contestAlias: 'testContestAlias',
        initialPoints: 100,
        initialProblems: [
          { ...problem, has_submissions: false },
          { ...problem, alias: 'problem_2', letter: 'B', title: 'Problem 2' },
        ],
      },
    });

    await wrapper
      .find('button[data-update-problem="problem"]')
      .trigger('click');

    wrapper.setData({ selectedRevision: revision, versionLog });

    await wrapper
      .find('input[name="use-latest-version"][value="false"]')
      .trigger('click');

    expect(wrapper.find('[data-versions]').text()).toContain(
      commit.substr(0, 8),
    );
    expect(wrapper.find('[data-versions]').text()).toContain(
      alternativeCommit.substr(0, 8),
    );

    await wrapper
      .find(`tr[data-revision="${alternativeCommit}"]`)
      .trigger('click');

    await wrapper.find('button.add-problem').trigger('click');
    expect(wrapper.emitted('add-problem')).toEqual([
      [
        {
          isUpdate: true,
          problem: {
            order: 1,
            points: 100,
            alias: 'problem',
            commit: alternativeCommit,
          },
        },
      ],
    ]);
  });

  it('Should update latest version for a problem in the list', async () => {
    const alternativeCommit = 'b6918b04024976471c4cac1d2efeb81021d93345';
    const versionLog: types.ProblemVersion[] = [
      { ...revision },
      { ...revision, commit: alternativeCommit },
    ];

    const wrapper = mount(contest_AddProblem, {
      propsData: {
        contestAlias: 'testContestAlias',
        initialPoints: 100,
        initialProblems: [
          { ...problem, has_submissions: false },
          { ...problem, alias: 'problem_2', letter: 'B', title: 'Problem 2' },
        ],
      },
    });

    await wrapper
      .find('button[data-update-problem="problem"]')
      .trigger('click');

    wrapper.setData({ versionLog });

    await wrapper
      .find('input[name="use-latest-version"][value="true"]')
      .trigger('click');

    await wrapper.find('button.add-problem').trigger('click');
    expect(wrapper.emitted('add-problem')).toEqual([
      [
        {
          isUpdate: true,
          problem: {
            order: 1,
            points: 100,
            alias: 'problem',
            commit: undefined,
          },
        },
      ],
    ]);
  });
});
