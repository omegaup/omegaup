import { shallowMount } from '@vue/test-utils';
import { types } from '../../api_types';

import T from '../../lang';

import course_AddStudents from './AddStudents.vue';

describe('AddStudents.vue', () => {
  it('Should handle empty students list', () => {
    const wrapper = shallowMount(course_AddStudents, {
      propsData: {
        courseAlias: 'course_alias',
        students: [] as types.CourseStudent[],
        identityRequests: [] as types.IdentityRequest[],
      },
    });

    expect(wrapper.text()).toContain(T.courseEditAddStudentsAdd);
    expect(wrapper.text()).toContain(T.courseStudentsEmpty);
  });

  it('Should handle students list and requests list', () => {
    const wrapper = shallowMount(course_AddStudents, {
      propsData: {
        courseAlias: 'course_alias',
        students: [
          {
            name: 'omegaUp user',
            username: 'user',
          },
        ] as types.CourseStudent[],
        identityRequests: [
          {
            accepted: false,
            country: 'mx',
            last_update: undefined,
            request_time: new Date(),
            username: 'user_1',
          },
        ] as types.IdentityRequest[],
      },
    });

    expect(wrapper.text()).toContain(T.courseEditAddStudentsAdd);
    expect(wrapper.text()).toContain('omegaUp user');
  });
});
