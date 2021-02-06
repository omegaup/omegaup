import { shallowMount } from '@vue/test-utils';

import T from '../../lang';
import type { types } from '../../api_types';

import course_CardsList from './CardsList.vue';

const coursesListProps = {
  courses: {
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
              description: 'Test description',
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
  } as types.StudentCourses,
};

describe('CardsList.vue', () => {
  it('Should handle empty courses list for user', () => {
    const wrapper = shallowMount(course_CardsList, {
      propsData: coursesListProps,
    });

    expect(wrapper.find('.public').text()).toContain(T.courseListPublicCourses);
    expect(wrapper.find('.student').text()).toContain(T.courseListIStudy);
  });
});
