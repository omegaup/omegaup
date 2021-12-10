jest.mock('../../../../third_party/js/diff_match_patch.js');

import { shallowMount, mount } from '@vue/test-utils';
import { types } from '../../api_types';
import T from '../../lang';
import arena_Course from './Coursev2.vue';

describe('Coursev2.vue', () => {
  const course: types.ArenaCourseDetails = {
    alias: 'test-course',
    name: 'Test Course',
  };
  const assignment: types.ArenaCourseAssignment = {
    alias: 'test-assignment',
    name: 'Test Assignment',
    description: 'Test assignment description',
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

  it('Should show the course summary', () => {
    const wrapper = shallowMount(arena_Course, {
      propsData: {
        course,
        assignment,
        problems,
        currentProblem: null,
        scoreboard,
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
        course,
        assignment,
        problems,
        currentProblem: null,
        selectedTab: 'ranking',
        scoreboard,
      },
    });

    expect(wrapper.text()).toContain(T.wordsRanking);
    expect(wrapper.text()).toContain(scoreboard.ranking[0].username);
  });

  it('Should hide the course scoreboard tab when scoreboard is null', () => {
    const wrapper = mount(arena_Course, {
      propsData: {
        course,
        assignment,
        problems,
        currentProblem: null,
        selectedTab: 'ranking',
        scoreboard: null,
      },
    });

    expect(wrapper.text()).not.toContain(T.wordsRanking);
  });
});
