import { shallowMount } from '@vue/test-utils';
import expect from 'expect';
import Vue from 'vue';

import T from '../../lang';
import { omegaup } from '../../omegaup';
import { types } from '../../api_types';

import course_Details from './Details.vue';

describe('Details.vue', () => {
  it('Should handle empty assignments and progress as admin', () => {
    const courseName = 'Test course';
    const wrapper = shallowMount(course_Details, {
      propsData: {
        course: <omegaup.Course>{
          admission_mode: 'registration',
          alias: 'test-course',
          assignments: <omegaup.Assignment[]>[],
          basic_information_required: false,
          description: '# Test',
          finish_time: null,
          is_curator: true,
          is_admin: true,
          name: courseName,
          public: true,
          requests_user_information: 'no',
          school_name: '',
          show_scoreboard: false,
          start_time: new Date(),
          student_count: 1,
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
        course: <omegaup.Course>{
          admission_mode: 'registration',
          alias: 'test-course',
          assignments: <omegaup.Assignment[]>[],
          basic_information_required: false,
          description: '# Test',
          finish_time: null,
          is_curator: false,
          is_admin: false,
          name: courseName,
          public: true,
          requests_user_information: 'no',
          school_name: '',
          show_scoreboard: false,
          start_time: new Date(),
          student_count: 1,
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
        course: <omegaup.Course>{
          admission_mode: 'registration',
          alias: 'test-course',
          assignments: <omegaup.Assignment[]>[
            <omegaup.Assignment>{
              alias: 'test-assignment',
              assignment_type: 'homework',
              description: '',
              finish_time: null,
              name: 'Test',
              order: 1,
              scoreboard_url: '',
              scoreboard_url_admin: '',
              start_time: new Date(0),
            },
          ],
          basic_information_required: false,
          description: '# Test',
          finish_time: null,
          is_curator: true,
          is_admin: true,
          name: courseName,
          public: true,
          requests_user_information: 'no',
          school_name: '',
          show_scoreboard: false,
          start_time: new Date(),
          student_count: 1,
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
