jest.mock('../../../../third_party/js/diff_match_patch.js');

import { mount } from '@vue/test-utils';

import arena_Course from './Course.vue';
import { types } from '../../api_types';

describe('Course.vue', () => {
  const currentAssignment: types.CourseAssignment = {
    alias: 'Tarea de prueba',
    assignment_type: 'homework',
    description: 'Descripción de la tarea de prueba',
    finish_time: new Date(),
    name: 'Tarea de prueba',
    start_time: new Date(),
    has_runs: true,
    max_points: 100,
    order: 1,
    problemset_id: 1,
    scoreboard_url: '',
    scoreboard_url_admin: '',
  };

  const course: types.CourseDetails = {
    admission_mode: 'registration',
    alias: 'test-course',
    archived: false,
    assignments: [currentAssignment],
    clarifications: [],
    needs_basic_information: false,
    description: '# Test',
    finish_time: new Date(),
    is_curator: true,
    is_admin: true,
    name: 'Curso de prueba',
    requests_user_information: 'no',
    school_name: '',
    show_scoreboard: false,
    start_time: new Date(),
    student_count: 1,
    unlimited_duration: false,
  };

  const scoreboard = {
    finish_time: new Date(0),
    problems: [
      {
        alias: 'problem_1',
        order: 1,
      },
      {
        alias: 'problem_2',
        order: 2,
      },
      {
        alias: 'problem_3',
        order: 3,
      },
    ],
    ranking: [
      {
        classname: 'user-rank-unranked',
        country: 'MX',
        is_invited: true,
        problems: [
          {
            alias: 'problem_1',
            penalty: 20,
            percent: 1,
            points: 100,
            runs: 1,
          },
          {
            alias: 'problem_2',
            penalty: 10,
            percent: 1,
            points: 100,
            runs: 4,
          },
          {
            alias: 'problem_3',
            penalty: 30,
            percent: 1,
            points: 100,
            runs: 5,
          },
        ],
        total: {
          penalty: 20,
          points: 100,
        },
        username: 'omegaUp',
      },
    ],
    start_time: new Date(0),
    time: new Date(0),
    title: 'omegaUp',
  };

  it('Should handle course in arena', async () => {
    const wrapper = mount(arena_Course, {
      propsData: {
        activeTab: 'problems',
        clarifications: [],
        course,
        currentAssignment,
        guid: null,
        problem: null,
        problemAlias: null,
        problemInfo: null,
        problems: [],
        showNewClarificationPopup: false,
        socketConnected: true,
        users: [],
        scoreboard,
      },
    });

    expect(wrapper.find('h2').text()).toContain(currentAssignment.name);
    expect(wrapper.find('.clock').text()).not.toBe('∞');
  });
});
