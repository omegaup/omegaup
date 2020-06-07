import { shallowMount } from '@vue/test-utils';
import expect from 'expect';
import Vue from 'vue';

import T from '../../lang';
import { omegaup } from '../../omegaup';

import course_Details from './Details.vue';

describe('Details.vue', () => {
  it('Should handle empty assignments and progress', () => {
    const courseName = 'Test course';
    const wrapper = shallowMount(course_Details, {
      propsData: {
        course: {
          admission_mode: 'registration',
          alias: 'test-course',
          assignments: [],
          basic_information_required: false,
          description: '# Test',
          finish_time: null,
          isCurator: true,
          is_admin: true,
          name: courseName,
          requests_user_information: 'no',
          school_id: 0,
          show_scoreboard: false,
          start_time: new Date(),
          student_count: 1,
        },
        progress: [],
      },
    });

    expect(wrapper.text()).toContain(courseName);
  });
});
