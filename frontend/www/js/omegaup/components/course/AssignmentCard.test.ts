import { mount } from '@vue/test-utils';

import T from '../../lang';
import type { types } from '../../api_types';

import course_AssignmentCard from './AssignmentCard.vue';

describe('AssignmentCard.vue', () => {
  const assignment: types.CourseAssignment = {
    alias: 'test-assignment',
    assignment_type: 'homework',
    description: '',
    finish_time: undefined,
    name: 'Test',
    order: 1,
    scoreboard_url: '',
    scoreboard_url_admin: '',
    start_time: new Date(),
    has_runs: false,
    max_points: 0,
    problemset_id: 0,
    problemCount: 0,
  };

  it('Should handle assignment details', () => {
    const studentProgress = 50;
    const wrapper = mount(course_AssignmentCard, {
      propsData: {
        courseAlias: 'test-course',
        assignment,
        studentProgress,
      },
    });
    expect(wrapper.text()).toContain(assignment.name);
    expect(wrapper.text()).toContain(T.wordsHomework);
    expect(wrapper.text()).toContain(T.courseCardCourseResume);
    expect(wrapper.text()).toContain(`${studentProgress}%`);
  });
});
