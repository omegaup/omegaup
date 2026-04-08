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
    opened: false,
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
    expect(wrapper.text()).toContain(T.assignmentCardStart);
    expect(wrapper.text()).toContain(`${studentProgress}%`);
  });

  it('Should handle lesson details', () => {
    const lecture: types.CourseAssignment = {
      ...assignment,
      assignment_type: 'lesson',
      opened: true,
    };
    const studentProgress = 50;
    const wrapper = mount(course_AssignmentCard, {
      propsData: {
        courseAlias: 'test-course',
        assignment: lecture,
        studentProgress,
      },
    });
    expect(wrapper.text()).toContain(assignment.name);
    expect(wrapper.text()).toContain(T.wordsLesson);
    expect(wrapper.text()).toContain(T.courseCardCourseResume);
    expect(wrapper.text()).not.toContain(`${studentProgress}%`);
  });

  it('Should display due date when finish_time is set', () => {
    const futureDate = new Date();
    futureDate.setDate(futureDate.getDate() + 7);
    const assignmentWithDeadline: types.CourseAssignment = {
      ...assignment,
      finish_time: futureDate,
    };
    const wrapper = mount(course_AssignmentCard, {
      propsData: {
        courseAlias: 'test-course',
        assignment: assignmentWithDeadline,
        studentProgress: 50,
      },
    });
    expect(wrapper.text()).toContain(T.wordsDueDate);
    expect(wrapper.find('.due-date').exists()).toBe(true);
    expect(wrapper.find('.badge-danger').exists()).toBe(false);
  });

  it('Should not display due date when finish_time is undefined', () => {
    const wrapper = mount(course_AssignmentCard, {
      propsData: {
        courseAlias: 'test-course',
        assignment,
        studentProgress: 50,
      },
    });
    expect(wrapper.find('.due-date').exists()).toBe(false);
  });

  it('Should show overdue badge when finish_time is in the past', () => {
    const pastDate = new Date();
    pastDate.setDate(pastDate.getDate() - 1);
    const overdueAssignment: types.CourseAssignment = {
      ...assignment,
      finish_time: pastDate,
    };
    const wrapper = mount(course_AssignmentCard, {
      propsData: {
        courseAlias: 'test-course',
        assignment: overdueAssignment,
        studentProgress: 50,
      },
    });
    expect(wrapper.text()).toContain(T.wordsDueDate);
    expect(wrapper.find('.badge-danger').exists()).toBe(true);
    expect(wrapper.text()).toContain(T.wordsOverdue);
  });
});
