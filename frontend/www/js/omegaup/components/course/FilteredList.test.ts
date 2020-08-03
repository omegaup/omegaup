import { shallowMount } from '@vue/test-utils';
import expect from 'expect';
import Vue from 'vue';

import T from '../../lang';
import { omegaup } from '../../omegaup';
import { types } from '../../api_types';

import course_FilteredList from './FilteredList.vue';

const noop = () => {};
Object.defineProperty(window, 'scrollTo', { value: noop, writable: true });

describe('FilteredList.vue', () => {
  it('Should handle filtered courses', async () => {
    const courseName = 'Test course';
    const wrapper = shallowMount(course_FilteredList, {
      propsData: {
        activeTab: 'past',
        courses: <types.CoursesByAccessMode>{
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
        },
      },
    });

    expect(wrapper.text()).toContain(T.courseListPastCourses);
  });
});
