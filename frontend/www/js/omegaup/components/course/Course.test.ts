import { mount } from '@vue/test-utils';
import expect from 'expect';

import T from '../../lang';
import type { types } from '../../api_types';

import course_Course from './Course.vue';

describe('Course.vue', () => {
  it('Should handle a card with public course', () => {
    const wrapper = mount(course_Course, {
      propsData: {
        courseName: 'Introductorio a la OMI',
        courseAlias: 'OMI',
        schoolName: 'omegaUp',
        finishTime: null,
        progress: 0,
        content: [
          {
            alias: 't1',
            assignment_type: 'homework',
            description: 'assigment description',
            finish_time: new Date(),
            has_runs: false,
            max_points: 100,
            name: 'Introducción a omegaUp',
            order: 1,
            problemset_id: 1,
            scoreboard_url: 'url',
            scoreboard_url_admin: 'admin_url',
            start_time: new Date(),
          },
          {
            alias: 't2',
            assignment_type: 'homework',
            description: 'assigment description',
            finish_time: new Date(),
            has_runs: false,
            max_points: 100,
            name: 'Estructura de datos',
            order: 2,
            problemset_id: 2,
            scoreboard_url: 'url',
            scoreboard_url_admin: 'admin_url',
            start_time: new Date(),
          },
        ] as types.CourseAssignment[],
        loggedIn: true,
        isOpen: false,
        showTopics: true,
      },
    });

    expect(wrapper.text()).toContain(T.startCourse);
    expect(wrapper.text()).toContain(T.courseCardShowTopics);
    expect(wrapper.text()).toContain(T.wordsUnlimitedDuration);
    expect(wrapper.text()).toContain('Introductorio a la OMI');
    expect(wrapper.text()).toContain('Introducción a omegaUp');
    expect(wrapper.text()).toContain('Estructura de datos');
  });

  it('Should handle a card with private course', () => {
    const now = new Date();
    const finishTime = new Date(now.getFullYear(), now.getMonth() + 1, 1);

    const wrapper = mount(course_Course, {
      propsData: {
        courseName: 'Clase 2020 semestre 1',
        courseAlias: 'S1-2020',
        schoolName: 'omegaUp',
        finishTime: finishTime,
        progress: 70,
        content: [],
        isOpen: true,
        loggedIn: true,
        showTopics: false,
      },
    });

    expect(wrapper.text()).toContain(T.courseCardCourseResume);
    expect(wrapper.text()).not.toContain(T.courseCardShowTopics);
    expect(wrapper.text()).toContain(T.wordsProgress);
    expect(wrapper.text()).toContain('Clase 2020 semestre 1');
  });
});
