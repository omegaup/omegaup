import { shallowMount } from '@vue/test-utils';
import expect from 'expect';

import T from '../../lang';
import { types } from '../../api_types';

import course_List from './List.vue';

const coursesListProps = {
  courses: <types.StudentCourses>{
    public: {
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
              finish_time: new Date(),
              name: 'Curso de introducción',
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
    },
    student: {
      accessMode: 'student',
      activeTab: 'current',
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

describe('List.vue', () => {
  it('Should handle empty courses list for user', () => {
    const wrapper = shallowMount(course_List, {
      propsData: coursesListProps,
    });

    expect(wrapper.find('.public').text()).toContain(T.courseListPublicCourses);
    expect(wrapper.find('.student').text()).toContain(T.courseListIStudy);
  });
});
