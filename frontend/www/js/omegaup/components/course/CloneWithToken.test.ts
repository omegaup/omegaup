import { shallowMount } from '@vue/test-utils';
import expect from 'expect';

import { types } from '../../api_types';

import course_CloneWithToken from './CloneWithToken.vue';

describe('CloneWithToken.vue', () => {
  it('Should handle empty assignments and progress as admin', () => {
    const courseName = 'Test course';
    const wrapper = shallowMount(course_CloneWithToken, {
      propsData: {
        course: <types.CourseDetails>{
          admission_mode: 'private',
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
        username: 'omegaup',
        classname: 'user-rank-unranked',
        token: 'fak3T0k3n',
      },
    });

    expect(wrapper.text()).toContain(courseName);
  });
});
