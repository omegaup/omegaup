import { shallowMount } from '@vue/test-utils';
import expect from 'expect';
import Vue from 'vue';

import T from '../../lang';
import { types } from '../../api_types';

import course_List from './List.vue';

const coursesListProps = {
  courses: <types.AllCourses>{
    admin: {
      accessMode: 'admin',
      activeTab: '',
      filteredCourses: {
        current: {
          courses: [],
          timeType: 'current',
        },
        past: {
          courses: [],
          timeType: 'past',
        },
      },
    },
    public: {
      accessMode: 'public',
      activeTab: '',
      filteredCourses: {
        current: {
          courses: [],
          timeType: 'current',
        },
        past: {
          courses: [],
          timeType: 'past',
        },
      },
    },
    student: {
      accessMode: 'student',
      activeTab: '',
      filteredCourses: {
        current: {
          courses: [],
          timeType: 'current',
        },
        past: {
          courses: [],
          timeType: 'past',
        },
      },
    },
  },
  isMainUserIdentity: true,
};

describe('List.vue', () => {
  it('Should handle empty courses list for user', () => {
    const wrapper = shallowMount(course_List, {
      propsData: coursesListProps,
    });

    expect(wrapper.text()).toContain(T.courseList);
    expect(wrapper.find('.card a.btn-primary').text()).toContain(T.courseNew);
  });

  it('Should handle empty courses list for identity', () => {
    const wrapper = shallowMount(course_List, {
      propsData: Object.assign({}, coursesListProps, {
        isMainUserIdentity: false,
      }),
    });

    expect(wrapper.text()).toContain(T.courseList);
    expect(wrapper.find('.card a.btn-primary').exists()).toBeFalsy();
  });
});
