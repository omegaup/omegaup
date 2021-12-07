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
        selectedAssignment: {
          alias: 'testassignment',
          assignment_type: 'homework',
          description: 'Test assignment',
          finish_time: null,
          name: 'Test assignment',
          order: 1,
          scoreboard_url: '',
          scoreboard_url_admin: '',
          start_time: new Date(),
        } as omegaup.Assignment,
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
        selectedAssignment: {
          alias: 'testassignment',
          assignment_type: 'homework',
          description: 'Test assignment',
          finish_time: null,
          name: 'Test assignment',
          order: 1,
          scoreboard_url: '',
          scoreboard_url_admin: '',
          start_time: new Date(),
        } as omegaup.Assignment,
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
});
