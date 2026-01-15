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
          clarifications: [],
          objective: 'Objetivo de prueba',
          level: '',
          needs_basic_information: false,
          description: '# Test',
          finish_time: new Date(),
          is_curator: true,
          is_admin: true,
          is_teaching_assistant: false,
          name: courseName,
          public: true,
          recommended: false,
          requests_user_information: 'no',
          school_name: '',
          show_scoreboard: false,
          start_time: new Date(),
          student_count: 1,
          unlimited_duration: false,
          teaching_assistant_enabled: false,
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
          clarifications: [],
          objective: 'Objetivo de prueba',
          level: '',
          needs_basic_information: false,
          description: '# Test',
          finish_time: new Date(),
          is_curator: false,
          is_admin: false,
          is_teaching_assistant: false,
          name: courseName,
          public: true,
          recommended: false,
          requests_user_information: 'no',
          school_name: '',
          show_scoreboard: false,
          start_time: new Date(),
          student_count: 1,
          unlimited_duration: false,
          teaching_assistant_enabled: false,
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
              opened: false,
              order: 1,
              scoreboard_url: '',
              scoreboard_url_admin: '',
              start_time: new Date(0),
              has_runs: false,
              max_points: 0,
              problemset_id: 0,
              problemCount: 0,
            },
          ],
          clarifications: [],
          objective: 'Objetivo de prueba',
          level: '',
          needs_basic_information: false,
          description: '# Test',
          finish_time: undefined,
          is_curator: true,
          is_admin: true,
          is_teaching_assistant: false,
          name: courseName,
          public: true,
          recommended: false,
          requests_user_information: 'no',
          school_name: '',
          show_scoreboard: false,
          start_time: new Date(),
          student_count: 1,
          unlimited_duration: true,
          teaching_assistant_enabled: false,
        } as types.CourseDetails,
        progress: {
          'test-assignment': {
            score: 0,
            max_score: 1,
          } as types.Progress,
        } as types.AssignmentProgress,
      },
    });

    expect(wrapper.text()).toContain(T.wordsCloneThisCourse);
  });
});
