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
          assignmentProblems: [],
          selectedAssignment: null,
          students: [],
          identityRequests: [],
          admins: [],
          groupsAdmins: [],
        },
        initialTab: 'course',
      },
    });

    expect(wrapper.text()).toContain(courseName);

    // All the links are available
    await wrapper.find('a[data-tab-assignments]').trigger('click');
    await wrapper.find('a[data-tab-problems]').trigger('click');
    await wrapper.find('a[data-tab-admission-mode]').trigger('click');
    await wrapper.find('a[data-tab-students]').trigger('click');
    await wrapper.find('a[data-tab-admins]').trigger('click');
    await wrapper.find('a[data-tab-clone]').trigger('click');
    await wrapper.find('a[data-tab-course]').trigger('click');
  });
});
