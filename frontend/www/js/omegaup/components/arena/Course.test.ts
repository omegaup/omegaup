jest.mock('../../../../third_party/js/diff_match_patch.js');

import { mount } from '@vue/test-utils';

import arena_Course from './Course.vue';
import { types } from '../../api_types';

describe('Course.vue', () => {
  const currentAssignment: types.ArenaAssignment = {
    alias: 'Tarea de prueba',
    assignment_type: 'homework',
    description: 'Descripción de la tarea de prueba',
    director: 'Director',
    problemset_id: 1,
    finish_time: new Date(),
    name: 'Tarea de prueba',
    start_time: new Date(),
    problems: [],
    runs: [],
    totalRuns: 0,
  };

  const course: types.CourseDetails = {
    admission_mode: 'registration',
    alias: 'test-course',
    archived: false,
    assignments: [
      {
        alias: 'Tarea de prueba',
        assignment_type: 'homework',
        description: 'Descripción de la tarea de prueba',
        problemset_id: 1,
        finish_time: new Date(),
        name: 'Tarea de prueba',
        start_time: new Date(),
        has_runs: false,
        max_points: 100,
        opened: false,
        order: 1,
        scoreboard_url: 'scoreboard_url',
        scoreboard_url_admin: 'scoreboard_url_admin',
        problemCount: 0,
      },
    ],
    clarifications: [],
    needs_basic_information: false,
    description: '# Test',
    objective: 'Objetivo de prueba',
    level: '',
    finish_time: new Date(),
    is_curator: true,
    is_admin: false,
    is_teaching_assistant: false,
    name: 'Curso de prueba',
    recommended: false,
    requests_user_information: 'no',
    school_name: '',
    show_scoreboard: false,
    start_time: new Date(),
    student_count: 1,
    unlimited_duration: false,
    teaching_assistant_enabled: false,
  };

  const scoreboard: types.Scoreboard = {
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

  const problemInfo: types.ProblemDetails = {
    accepted: 4,
    accepts_submissions: true,
    alias: 'test',
    allow_user_add_tags: false,
    commit: '123',
    creation_date: new Date(),
    email_clarifications: true,
    input_limit: 10240,
    karel_problem: false,
    languages: ['py2', 'py3'],
    limits: {
      input_limit: '10 KiB',
      memory_limit: '32 MiB',
      overall_wall_time_limit: '1s',
      time_limit: '1s',
    },
    nominationStatus: {
      alreadyReviewed: false,
      canNominateProblem: false,
      dismissed: false,
      dismissedBeforeAc: false,
      language: 'py2',
      nominated: false,
      nominatedBeforeAc: false,
      solved: true,
      tried: true,
    },
    order: 'sum',
    points: 100,
    problem_id: 1,
    quality_seal: true,
    score: 100,
    settings: {
      cases: {
        statement_001: {
          in: '6\n2 3 2 3 2 4',
          out: '10',
          weight: 1,
        },
      },
      limits: {
        ExtraWallTime: '0s',
        MemoryLimit: 33554432,
        OutputLimit: 10240,
        OverallWallTimeLimit: '1s',
        TimeLimit: '1s',
      },
      validator: {
        name: 'token-numeric',
        tolerance: 1e-9,
      },
    },
    show_diff: 'none',
    statement: {
      images: {},
      sources: {},
      language: 'es',
      markdown: '# test',
    },
    submissions: 5,
    title: '',
    version: '123',
    visibility: 1,
    visits: 5,
  };

  const problem: types.NavbarProblemsetProblem = {
    acceptsSubmissions: true,
    alias: 'test',
    bestScore: 100,
    hasRuns: true,
    maxScore: 100,
    text: 'Problem Test',
  };

  const run: types.RunWithDetails = {
    alias: 'test',
    classname: 'user-rank-unranked',
    contest_score: 100,
    country: 'xx',
    guid: '78099022574726af861839e1b4210188',
    language: 'py3',
    memory: 0,
    penalty: 0,
    runtime: 0,
    score: 1,
    status: 'ready',
    submit_delay: 0,
    time: new Date(),
    type: 'normal',
    username: 'course_test_user_1',
    verdict: 'AC',
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
        users: [],
        scoreboard,
      },
    });

    expect(wrapper.find('h2').text()).toContain(currentAssignment.name);
    expect(wrapper.find('.clock').text()).not.toBe('∞');
  });

  it('Should emit reset-hash function in arena course', async () => {
    const wrapper = mount(arena_Course, {
      propsData: {
        activeTab: 'problems',
        clarifications: [],
        course,
        currentAssignment,
        problem,
        problemAlias: problem.alias,
        problemInfo,
        problems: [problem],
        showNewClarificationPopup: false,
        users: [] as types.ContestUser[],
        scoreboard,
      },
    });

    await wrapper.setProps({ problem: null });
    expect(wrapper.emitted('reset-hash')).toEqual([
      [{ alias: null, selectedTab: 'problems' }],
    ]);
  });

  it('Should handle run details button as student', async () => {
    const wrapper = mount(arena_Course, {
      propsData: {
        activeTab: 'problems',
        clarifications: [],
        course,
        currentAssignment,
        problem,
        problemAlias: problem.alias,
        problemInfo,
        problems: [problem],
        runs: [run],
        showNewClarificationPopup: false,
        users: [] as types.ContestUser[],
        scoreboard,
      },
    });

    await wrapper
      .find(`button[data-run-details="${run.guid}"]`)
      .trigger('click');
    expect(wrapper.emitted('show-run')).toEqual([
      [
        {
          guid: '78099022574726af861839e1b4210188',
          hash: '#problems/test/show-run:78099022574726af861839e1b4210188',
          isAdmin: false,
        },
      ],
    ]);
  });

  it('Should handle run details button as admin', async () => {
    const wrapper = mount(arena_Course, {
      propsData: {
        activeTab: 'runs',
        clarifications: [],
        course: { ...course, ...{ is_admin: true } },
        currentAssignment,
        problem,
        problemAlias: problem.alias,
        problemInfo,
        problems: [problem],
        allRuns: [run],
        showNewClarificationPopup: false,
        users: [] as types.ContestUser[],
        scoreboard,
        showAllRuns: true,
      },
    });

    await wrapper.find('a[href="#runs"]').trigger('click');
    await wrapper.find('td div.dropdown>button.btn-secondary').trigger('click');
    await wrapper
      .find(`button[data-run-details="${run.guid}"]`)
      .trigger('click');

    expect(wrapper.emitted('show-run')).toEqual([
      [
        {
          guid: '78099022574726af861839e1b4210188',
          hash: '#runs/all/show-run:78099022574726af861839e1b4210188',
          isAdmin: true,
        },
      ],
    ]);
  });
});
