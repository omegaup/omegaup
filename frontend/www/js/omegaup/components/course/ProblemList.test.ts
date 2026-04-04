import { shallowMount } from '@vue/test-utils';

import T from '../../lang';
import { omegaup } from '../../omegaup';
import type { types } from '../../api_types';

import course_ProblemLists from './ProblemList.vue';

describe('ProblemLists.vue', () => {
  const problem: types.ProblemsetProblem = {
    accepted: 1,
    accepts_submissions: true,
    alias: 'testproblem',
    commit: 'testcommit',
    difficulty: 1,
    has_submissions: true,
    input_limit: 100,
    is_extra_problem: true,
    languages: 'cpp',
    order: 1,
    points: 100,
    quality_seal: false,
    submissions: 1,
    title: 'Test problem',
    version: '2',
    visibility: 1,
    visits: 2,
  };

  const selectedAssignment: omegaup.Assignment = {
    alias: 'testassignment',
    assignment_type: 'homework',
    description: 'Test assignment',
    finish_time: null,
    name: 'Test assignment',
    order: 1,
    scoreboard_url: '',
    scoreboard_url_admin: '',
    start_time: new Date(),
  };

  it('Should handle empty assignments and problems', () => {
    const wrapper = shallowMount(course_ProblemLists, {
      propsData: {
        assignmentProblems: [] as types.ProblemsetProblem[],
        assignments: [] as omegaup.Assignment[],
        selectedAssignment: {} as omegaup.Assignment,
        taggedProblems: [] as omegaup.Problem[],
        assignmentFormMode: omegaup.AssignmentFormMode.New,
      },
    });

    expect(wrapper.text()).toContain(T.courseAddProblemsEditAssignmentDesc);
    expect(
      wrapper.find('[data-course-problemlist] .card-body').text(),
    ).toContain(T.courseAssignmentProblemsEmpty);
  });

  it('Should handle assignment extra problem', () => {
    const wrapper = shallowMount(course_ProblemLists, {
      propsData: {
        assignmentProblems: [problem],
        selectedAssignment,
        taggedProblems: [problem] as omegaup.Problem[],
        assignmentFormMode: omegaup.AssignmentFormMode.New,
      },
    });

    expect(wrapper.text()).toContain(T.courseAddProblemsEditAssignmentDesc);
    expect(wrapper.text()).toContain(T.courseExtraPointsProblem);
  });

  it('Should show the current version of a problem', async () => {
    const wrapper = shallowMount(course_ProblemLists, {
      propsData: {
        assignmentProblems: [problem],
        selectedAssignment,
        taggedProblems: [problem] as omegaup.Problem[],
        assignmentFormMode: omegaup.AssignmentFormMode.New,
      },
    });

    await wrapper.find('button[data-edit-problem-version]').trigger('click');
    expect(
      wrapper.find('input[data-use-latest-version-true]').element,
    ).toBeChecked();
    expect(
      wrapper.find('input[data-use-latest-version-false]').element,
    ).not.toBeChecked();
  });

  it('Should update the version of a problem', async () => {
    const wrapper = shallowMount(course_ProblemLists, {
      propsData: {
        assignmentProblems: [problem],
        selectedAssignment,
        taggedProblems: [problem] as omegaup.Problem[],
        assignmentFormMode: omegaup.AssignmentFormMode.New,
      },
    });

    const author: types.Signature = {
      email: 'omegaup@omageup.com',
      name: 'omegaup',
      time: new Date(),
    };

    const versionLog: types.ProblemVersion[] = [
      {
        author,
        commit: '6bd806e2cacbf16066492c6217505cc188571255',
        committer: author,
        message: 'Updated zip file',
        parents: [
          'e33b83aa72c1ef590e7aefe38d32888e04de5856',
          'df9f334e36d70898ebbecbda6f4e3294489d8dd5',
          '007718db936cae2384c4c1b2899ff43ae5e3c69e',
          '165fb31e11f3788d6584aac797ae9f9b3d1584d1',
        ],
        tree: {
          'settings.distrib.json': '4cf83e77f31f8c77bf88c344a6e1000da57ff4ce',
          'settings.json': 'f3446305866a2bb97527277dc35dd301a468d484',
          'statements/en.markdown': '13f38b3f4c2d13e5fbf6a4bc6a1b066f30c63c97',
        },
        version: 'c722c8f5be56ee9153b4de80d7e16c695561d9ac',
      },
      {
        author,
        commit: 'e33b83aa72c1ef590e7aefe38d32888e04de5856',
        committer: author,
        message: 'Updated zip file',
        parents: [
          '7b7610c06410f3b9953d299327316bad1b3ffba4',
          '007718db936cae2384c4c1b2899ff43ae5e3c69e',
          '165fb31e11f3788d6584aac797ae9f9b3d1584d1',
        ],
        tree: {
          'settings.distrib.json': '4cf83e77f31f8c77bf88c344a6e1000da57ff4ce',
          'settings.json': 'f3446305866a2bb97527277dc35dd301a468d484',
          'statements/en.markdown': '225efc207a325217d8035bbb0ce3007a8b21c6e9',
        },
        version: '00980d7064d76a21e2cf5ff5a6876814a84d092b',
      },
    ];

    const selectedRevision: types.ProblemVersion = versionLog[1];
    const publishedRevision: types.ProblemVersion = versionLog[1];

    await wrapper.find('button[data-edit-problem-version]').trigger('click');

    await wrapper.setData({ versionLog, selectedRevision, publishedRevision });

    expect(wrapper.vm.selectedRevision?.version).toEqual(versionLog[1].version);

    expect(
      wrapper.find('input[data-use-latest-version-true]').element,
    ).not.toBeChecked();
    expect(
      wrapper.find('input[data-use-latest-version-false]').element,
    ).toBeChecked();

    const radio = wrapper.find('input[name="use-latest-version"]')
      .element as HTMLOptionElement;
    radio.selected = true;
    await radio.click();

    expect(
      wrapper.find('input[data-use-latest-version-true]').element,
    ).toBeChecked();
    expect(
      wrapper.find('input[data-use-latest-version-false]').element,
    ).not.toBeChecked();

    expect(wrapper.vm.selectedRevision?.version).toEqual(versionLog[0].version);
  });
});
