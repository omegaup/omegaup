import { mount } from '@vue/test-utils';
import expect from 'expect';

import T from '../../lang';
import type { types } from '../../api_types';

import course_CardsList from './CardsList.vue';

const coursesListProps = {
  courses: {
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
  } as types.StudentCourses,
};

describe('CardsList.vue', () => {
  it('Should handle empty courses list for user', () => {
    const wrapper = mount(course_CardsList, {
      propsData: coursesListProps,
    });

    expect(wrapper.text()).toContain(T.courseCardAboutCourses);
    expect(wrapper.text()).toContain(
      T.courseCardDescriptionCourses.split('\n')[0],
    );
    expect(wrapper.text()).toContain(T.wordsReadMore);
  });
});
