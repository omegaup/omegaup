import { shallowMount } from '@vue/test-utils';
import { types } from '../../api_types';
import expect from 'expect';

import T from '../../lang';

import course_AddStudents from './AddStudents.vue';

describe('AddStudents.vue', () => {
  it('Should handle empty students list', () => {
    const wrapper = shallowMount(course_AddStudents, {
      propsData: {
        courseAlias: 'course_alias',
        students: <types.CourseStudent[]>[],
        identityRequests: <types.IdentityRequest[]>[],
      },
    });

    expect(wrapper.text()).toContain(T.courseStudentsEmpty);
  });

  it('Should handle students list and requests list', () => {
    const wrapper = shallowMount(course_AddStudents, {
      propsData: {
        courseAlias: 'course_alias',
        students: <types.CourseStudent[]>[
          {
            name: 'omegaUp user',
            username: 'user',
          },
        ],
        identityRequests: <types.IdentityRequest[]>[
          {
            accepted: false,
            country: 'mx',
            last_update: undefined,
            request_time: new Date(),
            username: 'user_1',
          },
        ],
      },
    });

    expect(wrapper.text()).toContain('omegaUp user');
  });
});
