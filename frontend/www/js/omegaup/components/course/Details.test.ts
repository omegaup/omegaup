import { shallowMount } from '@vue/test-utils';
import expect from 'expect';
import Vue from 'vue';

import T from '../../lang';
import { types } from '../../api_types';

import course_Details from './Details.vue';

describe('Details.vue', () => {
  it('Should handle empty assignments and progress as admin', () => {
    const courseName = 'Test course';
    const wrapper = shallowMount(course_Details, {
      propsData: {
        course: <types.CourseDetails>{
          admission_mode: 'registration',
          alias: 'test-course',
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
        },
        progress: <types.AssignmentProgress>{},
      },
    });

    expect(wrapper.text()).toContain(courseName);
    expect(wrapper.find('a[data-button-homework]').text()).toBe(
      T.wordsNewHomework,
    );
    expect(wrapper.find('a[data-button-exam]').text()).toBe(T.wordsNewExam);
  });

  it('Should handle empty assignments and progress as student', () => {
    const courseName = 'Test course';
    const wrapper = shallowMount(course_Details, {
      propsData: {
        course: <types.CourseDetails>{
          admission_mode: 'registration',
          alias: 'test-course',
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
        },
        progress: <types.AssignmentProgress>{},
      },
    });

    expect(wrapper.find('a[data-button-homework]').exists()).toBe(false);
    expect(wrapper.find('a[data-button-exam]').exists()).toBe(false);
  });

  it('Should handle assignments without finish_time', () => {
    const courseName = 'Test course';
    const wrapper = shallowMount(course_Details, {
      propsData: {
        course: <types.CourseDetails>{
          admission_mode: 'registration',
          alias: 'test-course',
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
          unlimited_duration: false,
        },
        progress: <types.AssignmentProgress>{
          'test-assignment': <types.Progress>{
            score: 0,
            max_score: 1,
          },
        },
      },
    });

    expect(
      wrapper.find('[data-homework-alias="test-assignment"]').text(),
    ).toContain('—');
  });
});
