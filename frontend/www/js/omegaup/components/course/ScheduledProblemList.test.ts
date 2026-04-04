import { shallowMount } from '@vue/test-utils';

import T from '../../lang';
import { omegaup } from '../../omegaup';
import type { types } from '../../api_types';

import course_ScheduledProblemLists from './ScheduledProblemList.vue';

describe('ScheduledProblemLists.vue', () => {
  it('Should handle empty assignments and problems', () => {
    const wrapper = shallowMount(course_ScheduledProblemLists, {
      propsData: {
        assignments: [] as types.CourseAssignment[],
        assignmentProblems: [] as types.ProblemsetProblem[],
        selectedAssignment: {
          problemset_id: 0,
          alias: '',
          description: '',
          name: '',
          has_runs: false,
          max_points: 0,
          start_time: new Date(),
          finish_time: new Date(),
          opened: false,
          order: 1,
          problems: [],
          scoreboard_url: '',
          scoreboard_url_admin: '',
          assignment_type: 'homework',
          problemCount: 0,
        } as types.CourseAssignment,
        taggedProblems: [] as omegaup.Problem[],
      },
    });

    expect(wrapper.text()).toContain(T.courseAddProblemsAddAssignmentDesc);
    expect(
      wrapper.find('[data-course-problemlist] .card-body').text(),
    ).toContain(T.courseAssignmentProblemsEmpty);
  });
});
