import { shallowMount } from '@vue/test-utils';

import T from '../../lang';
import type { types } from '../../api_types';

import course_Details from './Details.vue';

describe('Details.vue', () => {
  it('Should handle empty assignments and progress as admin', () => {
    const courseName = 'Test course';
    const wrapper = shallowMount(course_Details, {
      propsData: {
        course: {
          admission_mode: 'registration',
          alias: 'test-course',
          archived: false,
          assignments: [],
          needs_basic_information: false,
          description: '# Test',
          finish_time: new Date(),
          is_curator: true,
          is_admin: true,
          name: courseName,
          public: true,
          requests_user_information: 'no',
          school_name: '',
          show_scoreboard: false,
          start_time: new Date(),
          student_count: 1,
          unlimited_duration: false,
        } as types.CourseDetails,
        progress: {} as types.AssignmentProgress,
      },
    });

    expect(wrapper.text()).toContain(courseName);
    expect(wrapper.find('a[data-button-progress-students]').text()).toBe(
      T.courseStudentsProgress,
    );
    expect(wrapper.find('a[data-button-manage-students]').text()).toBe(
      T.wordsAddStudent,
    );
  });

  it('Should handle empty assignments and progress as student', () => {
    const courseName = 'Test course';
    const wrapper = shallowMount(course_Details, {
      propsData: {
        course: {
          admission_mode: 'registration',
          alias: 'test-course',
          archived: false,
          assignments: [],
          needs_basic_information: false,
          description: '# Test',
          finish_time: new Date(),
          is_curator: false,
          is_admin: false,
          name: courseName,
          public: true,
          requests_user_information: 'no',
          school_name: '',
          show_scoreboard: false,
          start_time: new Date(),
          student_count: 1,
          unlimited_duration: false,
        } as types.CourseDetails,
        progress: {} as types.AssignmentProgress,
      },
    });

    expect(
      wrapper.find('a[data-button-progress-students]').exists(),
    ).toBeFalsy();
    expect(wrapper.find('a[data-button-manage-students]').exists()).toBeFalsy();
    expect(wrapper.text()).not.toContain(T.wordsCloneThisCourse);
  });

  it('Should handle assignments without finish_time', () => {
    const courseName = 'Test course';
    const wrapper = shallowMount(course_Details, {
      propsData: {
        course: {
          admission_mode: 'public',
          alias: 'test-course',
          archived: false,
          assignments: [
            {
              alias: 'test-assignment',
              assignment_type: 'homework',
              description: '',
              finish_time: undefined,
              name: 'Test',
              order: 1,
              scoreboard_url: '',
              scoreboard_url_admin: '',
              start_time: new Date(0),
              has_runs: false,
              max_points: 0,
              problemset_id: 0,
            },
          ],
          needs_basic_information: false,
          description: '# Test',
          finish_time: undefined,
          is_curator: true,
          is_admin: true,
          name: courseName,
          public: true,
          requests_user_information: 'no',
          school_name: '',
          show_scoreboard: false,
          start_time: new Date(),
          student_count: 1,
          unlimited_duration: true,
        } as types.CourseDetails,
        progress: {
          'test-assignment': {
            score: 0,
            max_score: 1,
          } as types.Progress,
        } as types.AssignmentProgress,
      },
    });

    expect(
      wrapper.find('[data-content-alias="test-assignment"]').text(),
    ).toContain('—');
    expect(wrapper.text()).toContain(T.wordsCloneThisCourse);
  });
});
