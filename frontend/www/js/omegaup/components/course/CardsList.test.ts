import { mount } from '@vue/test-utils';
import expect from 'expect';
import type { types } from '../../api_types';

import course_CardsList from './CardsList.vue';

const publicCoursesTitles = ['Curso público actual', 'Curso público pasado'];
const studentCoursesTitles = [
  'Curso que estudio sin terminar',
  'Curso que estudio terminado',
];
const coursesListProps = {
  courses: {
    public: {
      accessMode: 'public',
      activeTab: 'current',
      filteredCourses: {
        current: {
          courses: [
            {
              alias: 'public1',
              counts: {
                homework: 2,
                lesson: 2,
                test: 1,
              },
              description: 'Test description',
              finish_time: new Date(),
              name: publicCoursesTitles[0],
              start_time: new Date(),
              admission_mode: 'public',
              assignments: [],
              is_open: true,
            },
          ],
          timeType: 'current',
        },
        past: {
          courses: [
            {
              alias: 'public2',
              counts: {
                homework: 2,
                lesson: 2,
                test: 1,
              },
              description: 'Test description',
              finish_time: new Date(),
              name: publicCoursesTitles[1],
              start_time: new Date(),
              admission_mode: 'public',
              assignments: [],
              is_open: true,
            },
          ],
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
          courses: [
            {
              alias: 'student1',
              counts: {
                homework: 2,
                lesson: 2,
                test: 1,
              },
              description: 'Test description',
              finish_time: new Date(),
              name: studentCoursesTitles[0],
              start_time: new Date(),
              admission_mode: 'public',
              assignments: [],
              is_open: true,
              progress: 99,
            },
            {
              alias: 'student2',
              counts: {
                homework: 2,
                lesson: 2,
                test: 1,
              },
              description: 'Test description',
              finish_time: new Date(),
              name: studentCoursesTitles[1],
              start_time: new Date(),
              admission_mode: 'public',
              assignments: [],
              is_open: true,
              progress: 100,
            },
          ],
          timeType: 'past',
        },
      },
    },
  } as types.StudentCourses,
};

describe('CardsList.vue', () => {
  it('Should list public courses', async () => {
    const wrapper = mount(course_CardsList, {
      propsData: {
        courses: coursesListProps.courses,
        type: 'public',
      },
    });

    expect(wrapper.text()).toContain(publicCoursesTitles[0]);
    expect(wrapper.text()).not.toContain(publicCoursesTitles[1]);

    await wrapper.find('a[data-see-all]').trigger('click');
    expect(wrapper.text()).toContain(publicCoursesTitles[0]);
    expect(wrapper.text()).toContain(publicCoursesTitles[1]);
  });

  it('Should list student courses', async () => {
    const wrapper = mount(course_CardsList, {
      propsData: {
        courses: coursesListProps.courses,
        type: 'student',
      },
    });

    expect(wrapper.text()).toContain(studentCoursesTitles[0]);
    expect(wrapper.text()).not.toContain(studentCoursesTitles[1]);

    await wrapper.find('a[data-see-all]').trigger('click');
    expect(wrapper.text()).toContain(studentCoursesTitles[0]);
    expect(wrapper.text()).toContain(studentCoursesTitles[1]);
  });
});
