import { mount, shallowMount } from '@vue/test-utils';

import { types } from '../../api_types';
import T from '../../lang';

import contest_AddProblem from './AddProblem.vue';

const commit = '54318b04024976471c4cac1d2efeb81021d9396b';
const alternativeCommit = 'b6918b04024976471c4cac1d2efeb81021d93345';
const revision: types.ProblemVersion = {
  author: {
    name: 'Problem Author',
    email: 'author@omegaup.org',
    time: new Date(0),
  },
  commit,
  committer: {
    name: 'Problem Author',
    email: 'author@omegaup.org',
    time: new Date(0),
  },
  message: 'Some message',
  parents: [],
  tree: {},
  version: commit,
};
const versionLog: types.ProblemVersion[] = [
  { ...revision },
  { ...revision, commit: alternativeCommit },
];
const problem: types.ProblemsetProblemWithVersions = {
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
  versions: {
    log: versionLog,
    published: commit,
  },
  visibility: 1,
  visits: 0,
};

describe('AddProblem.vue', () => {
  beforeAll(() => {
    const div = document.createElement('div');
    div.id = 'root';
    document.body.appendChild(div);
  });

  afterAll(() => {
    const rootDiv = document.getElementById('root');
    if (rootDiv) {
      document.removeChild(rootDiv);
    }
  });

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

    const removeIcon =
      'button[data-remove-problem="problem"] font-awesome-icon-stub';
    expect(wrapper.find(removeIcon).attributes().icon).toBe('trash');
    expect(wrapper.find(removeIcon).attributes().class).toBeFalsy();
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
    const removeIcon =
      'button[data-remove-problem-disabled="problem"] font-awesome-icon-stub';
    expect(wrapper.find(removeIcon).attributes().class).toContain('disabled');
    expect(wrapper.find(removeIcon).attributes().class).toContain(
      'text-secondary',
    );
  });

  it('Should update a problem in the list', async () => {
    const initialProblems: types.ProblemsetProblemWithVersions[] = [
      { ...problem, has_submissions: false },
      { ...problem, alias: 'problem_2', letter: 'B', title: 'Problem 2' },
    ];
    initialProblems[0].commit = alternativeCommit;
    initialProblems[0].version = alternativeCommit;
    initialProblems[0].versions.published = alternativeCommit;
    const wrapper = shallowMount(contest_AddProblem, {
      propsData: {
        contestAlias: 'testContestAlias',
        initialPoints: 100,
        initialProblems,
        searchResultProblems: [{ key: 'problem', value: 'problem title' }],
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
            commit: alternativeCommit,
          },
        },
      ],
    ]);
  });

  it('Should update non-latest version for a problem in the list', async () => {
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

    await wrapper
      .find('input[name="use-latest-version"][value="false"]')
      .trigger('click');

    expect(wrapper.find('[data-versions]').text()).toContain(
      commit.substring(0, 8),
    );
    expect(wrapper.find('[data-versions]').text()).toContain(
      alternativeCommit.substring(0, 8),
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
    const wrapper = mount(contest_AddProblem, {
      propsData: {
        attachTo: '#root',
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

    await wrapper
      .find('input[name="use-latest-version"][value="true"]')
      .trigger('click');

    await wrapper.find('button.add-problem').trigger('submit');
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

  it('Should update latest version for a problem in the list when it is explicitly selected', async () => {
    const wrapper = mount(contest_AddProblem, {
      propsData: {
        attachTo: '#root',
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

    await wrapper
      .find('input[name="use-latest-version"][value="false"]')
      .trigger('click');

    expect(wrapper.find('[data-versions]').text()).toContain(
      commit.substring(0, 8),
    );
    expect(wrapper.find('[data-versions]').text()).toContain(
      alternativeCommit.substring(0, 8),
    );

    await wrapper.find(`tr[data-revision="${commit}"]`).trigger('click');

    await wrapper.find('form button[type="submit"]').trigger('submit');
    expect(wrapper.emitted('add-problem')).toEqual([
      [
        {
          isUpdate: true,
          problem: {
            order: 1,
            points: 100,
            alias: 'problem',
            commit,
          },
        },
      ],
    ]);
  });
});
