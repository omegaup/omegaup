import { shallowMount } from '@vue/test-utils';
import { omegaup } from '../../omegaup';
import expect from 'expect';
import Vue from 'vue';

import T from '../../lang';

import course_AddStudents from './AddStudents.vue';

describe('AddStudents.vue', () => {
  it('Should handle empty students list', () => {
    const wrapper = shallowMount(course_AddStudents, {
      propsData: {
        courseAlias: 'course_alias',
        students: <omegaup.CourseStudent[]>[],
        data: <omegaup.IdentityRequest[]>[],
      },
    });

    expect(wrapper.text()).toContain(T.courseStudentsEmpty);
  });

  it('Should handle students list and requests list', () => {
    const wrapper = shallowMount(course_AddStudents, {
      propsData: {
        courseAlias: 'course_alias',
        students: <omegaup.CourseStudent[]>[
          {
            name: 'omegaUp user',
            progress: {},
            username: 'user',
          },
        ],
        data: <omegaup.IdentityRequest[]>[
          {
            accepted: false,
            country: 'mx',
            last_update: null,
            request_time: new Date(),
            username: 'user_1',
          },
        ],
      },
    });

    expect(wrapper.text()).toContain('omegaUp user');
  });
});
