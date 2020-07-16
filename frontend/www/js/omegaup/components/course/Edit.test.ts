import { shallowMount } from '@vue/test-utils';
import expect from 'expect';
import Vue from 'vue';

import T from '../../lang';
import { omegaup } from '../../omegaup';
import { types } from '../../api_types';

import course_Edit from './Edit.vue';

const noop = () => {};
Object.defineProperty(window, 'scrollTo', { value: noop, writable: true });

describe('Edit.vue', () => {
  it('Should handle empty assignments', async () => {
    const courseName = 'Test course';
    const wrapper = shallowMount(course_Edit, {
      propsData: {
        data: {
          course: <types.CourseDetails>{
            admission_mode: 'registration',
            alias: 'test-course',
            assignments: <types.CourseAssignment[]>[],
            basic_information_required: false,
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
          },
          assignmentProblems: [],
          selectedAssignment: null,
          students: [],
          identityRequests: [],
          admins: [],
          groupsAdmins: [],
        },
        initialTab: 'course',
        emptyAssignment: <types.CourseAssignment>{
          problemset_id: 0,
          alias: '',
          description: '',
          name: '',
          has_runs: false,
          max_points: 0,
          start_time: new Date(),
          finish_time: new Date(),
          order: 1,
          problems: [],
          scoreboard_url: '',
          scoreboard_url_admin: '',
          assignment_type: 'test',
        },
      },
    });

    expect(wrapper.text()).toContain(courseName);

    // All the links are available
    await wrapper.find('a[data-tab-assignments]').trigger('click');
    await wrapper.find('a[data-tab-admission-mode]').trigger('click');
    await wrapper.find('a[data-tab-students]').trigger('click');
    await wrapper.find('a[data-tab-admins]').trigger('click');
    await wrapper.find('a[data-tab-clone]').trigger('click');
    await wrapper.find('a[data-tab-course]').trigger('click');
  });
});
