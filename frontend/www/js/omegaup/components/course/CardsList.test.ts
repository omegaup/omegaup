import { shallowMount } from '@vue/test-utils';
import expect from 'expect';

import T from '../../lang';
import { types } from '../../api_types';

import course_CardsList from './CardsList.vue';

const coursesListProps = {
  courses: <types.StudentCourses>{
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
};

describe('CardsList.vue', () => {
  it('Should handle empty courses list for user', () => {
    const wrapper = shallowMount(course_CardsList, {
      propsData: coursesListProps,
    });

    expect(wrapper.text()).toContain(T.courseCardAboutCourses);
    expect(wrapper.text()).toContain(
      T.courseCardDescriptionCourses.split(
        /[<ul>|</ul>|<li>|</li>|<p>|</p>]+/,
      )[0],
    );
    expect(wrapper.text()).toContain(T.wordsReadMore);
  });
});
