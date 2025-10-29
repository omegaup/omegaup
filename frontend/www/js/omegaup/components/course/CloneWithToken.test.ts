import { shallowMount } from '@vue/test-utils';

import type { types } from '../../api_types';

import course_CloneWithToken from './CloneWithToken.vue';

describe('CloneWithToken.vue', () => {
  it('Should handle empty assignments and progress as admin', () => {
    const courseName = 'Test course';
    const wrapper = shallowMount(course_CloneWithToken, {
      propsData: {
        course: {
          admission_mode: 'private',
          alias: 'test-course',
          archived: false,
          assignments: [],
          clarifications: [],
          needs_basic_information: false,
          description: '# Test',
          objective: 'Objetivo de prueba',
          level: '',
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
        username: 'omegaup',
        classname: 'user-rank-unranked',
        token: 'fak3T0k3n',
      },
    });

    expect(wrapper.text()).toContain(courseName);
  });
});
