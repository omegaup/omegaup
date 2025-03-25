import { shallowMount } from '@vue/test-utils';

import T from '../../lang';
import type { types } from '../../api_types';

import course_FilteredList from './FilteredList.vue';

const noop = () => {};
const baseFilteredCoursesListProps = {
  activeTab: 'past',
  courses: {
    accessMode: 'public',
    activeTab: 'current',
    filteredCourses: {
      current: {
        courses: [
          {
            alias: 'CC',
            counts: {
              homework: 2,
              lesson: 2,
              test: 1,
            },
            description: 'Test description',
            finish_time: null,
            name: 'Curso de introducción',
            start_time: new Date(),
            admission_mode: 'public',
            assignments: [],
            is_open: true,
          },
          {
            alias: 'cpluplus',
            counts: {},
            description: 'Test description',
            finish_time: new Date(),
            name: 'Introducción a C++',
            start_time: new Date(),
            admission_mode: 'public',
            assignments: [],
            is_open: true,
          },
        ],
        timeType: 'current',
      },
      past: {
        courses: [],
        timeType: 'past',
      },
    },
  } as types.CoursesByAccessMode,
};
Object.defineProperty(window, 'scrollTo', { value: noop, writable: true });

describe('FilteredList.vue', () => {
  it('Should handle filtered courses for student', () => {
    const wrapper = shallowMount(course_FilteredList, {
      propsData: baseFilteredCoursesListProps,
    });

    expect(wrapper.text()).toContain(T.courseListPastCourses);
    expect(wrapper.text()).toContain(T.wordsCompletedPercentage);
  });

  it('Should handle filtered courses for admin', () => {
    const wrapper = shallowMount(course_FilteredList, {
      propsData: Object.assign({}, baseFilteredCoursesListProps, {
        showPercentage: false,
      }),
    });

    expect(wrapper.text()).toContain(T.courseListPastCourses);
    expect(wrapper.text()).not.toContain(T.wordsCompletedPercentage);
  });
});
