jest.mock('../../../../third_party/js/diff_match_patch.js');

import { shallowMount, mount } from '@vue/test-utils';
import { types } from '../../api_types';
import T from '../../lang';
import arena_Course from './Coursev2.vue';

describe('Coursev2.vue', () => {
  const course: types.ArenaCourseDetails = {
    alias: 'test-course',
    name: 'Test Course',
    assignments: [
      {
        alias: 'test-assignment-A',
        assignment_type: 'homework',
        description: 'Test assignment description',
        finish_time: undefined,
        name: 'Test Assignment A',
        order: 1,
        scoreboard_url: '',
        scoreboard_url_admin: '',
        start_time: new Date(),
        has_runs: false,
        max_points: 0,
        opened: false,
        problemset_id: 0,
        problemCount: 0,
      },
      {
        alias: 'test-assignment-B',
        assignment_type: 'homework',
        description: 'Test assignment description',
        finish_time: undefined,
        name: 'Test Assignment B',
        order: 2,
        scoreboard_url: '',
        scoreboard_url_admin: '',
        start_time: new Date(),
        has_runs: false,
        max_points: 0,
        opened: false,
        problemset_id: 0,
        problemCount: 0,
      },
    ],
  };
  const assignment: types.ArenaCourseAssignment = {
    alias: 'test-assignment-A',
    description: 'Test assignment description',
    name: 'Test Assignment A',
    problemset_id: 1,
  };
  const problems: types.ArenaCourseProblem[] = [
    {
      alias: 'test-problem-1',
      title: 'Test Problem 1',
      letter: 'A',
    },
    {
      alias: 'test-problem-2',
      title: 'Test Problem 2',
      letter: 'B',
    },
  ];

  const scoreboard: types.Scoreboard = {
    finish_time: new Date(0),
    problems: [
      {
        alias: 'test-problem-1',
        order: 1,
      },
      {
        alias: 'test-problem-2',
        order: 2,
      },
    ],
    ranking: [
      {
        classname: 'user-rank-unranked',
        country: 'MX',
        is_invited: true,
        problems: [
          {
            alias: 'test-problem-1',
            penalty: 20,
            percent: 1,
            points: 100,
            runs: 1,
          },
          {
            alias: 'test-problem-2',
            penalty: 10,
            percent: 1,
            points: 100,
            runs: 4,
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

  const runs: types.Run[] = [
    {
      alias: 'Hello',
      classname: 'user-rank-unranked',
      country: 'xx',
      execution: 'EXECUTION_FINISHED',
      guid: 'abcdefg',
      language: 'py3',
      memory: 0,
      output: 'OUTPUT_CORRECT',
      penalty: 0,
      runtime: 0,
      score: 1,
      status: 'ready',
      status_memory: 'MEMORY_AVAILABLE',
      status_runtime: 'RUNTIME_AVAILABLE',
      submit_delay: 0,
      time: new Date(),
      username: 'omegaUp',
      verdict: 'AC',
    },
  ];

  it('Should show the assignment navigation buttons', () => {
    let wrapper = shallowMount(arena_Course, {
      propsData: {
        allRuns: runs,
        course,
        assignment,
        problems,
        currentProblem: null,
        scoreboard,
        userRuns: runs,
      },
    });
    expect(wrapper.text()).toContain(course.assignments[1].name);

    wrapper = shallowMount(arena_Course, {
      propsData: {
        allRuns: runs,
        course,
        assignment: course.assignments[1],
        problems,
        currentProblem: null,
        scoreboard,
        userRuns: runs,
      },
    });
    expect(wrapper.text()).toContain(course.assignments[0].name);
  });

  it('Should show the course summary', () => {
    const wrapper = shallowMount(arena_Course, {
      propsData: {
        allRuns: runs,
        course,
        assignment,
        problems,
        currentProblem: null,
        scoreboard,
        userRuns: runs,
      },
    });

    expect(wrapper.text()).toContain(course.name);
    expect(wrapper.text()).toContain(assignment.name);
    expect(wrapper.text()).toContain(course.name);
    expect(wrapper.text()).toContain(problems[0].title);
    expect(wrapper.text()).toContain(problems[1].title);
  });

  it('Should show the course scoreboard', () => {
    const wrapper = mount(arena_Course, {
      propsData: {
        allRuns: runs,
        course,
        assignment,
        problems,
        currentProblem: null,
        selectedTab: 'ranking',
        scoreboard,
        userRuns: runs,
      },
    });

    expect(wrapper.text()).toContain(T.wordsRanking);
    expect(wrapper.text()).toContain(scoreboard.ranking[0].username);
  });

  it('Should hide the course scoreboard tab when scoreboard is null', () => {
    const wrapper = mount(arena_Course, {
      propsData: {
        allRuns: runs,
        course,
        assignment,
        problems,
        currentProblem: null,
        selectedTab: 'ranking',
        scoreboard: null,
        userRuns: runs,
      },
    });

    expect(wrapper.text()).not.toContain(T.wordsRanking);
  });
});
