jest.mock('../../../../third_party/js/diff_match_patch.js');

import { mount } from '@vue/test-utils';
import type { types } from '../../api_types';
import * as time from '../../time';

import arena_ContestPractice from './ContestPractice.vue';

describe('ContestPractice.vue', () => {
  const date = new Date();

  const contest: types.ContestPublicDetails = {
    admission_mode: 'public',
    alias: 'omegaUp',
    description: 'hello omegaUp',
    director: 'omegaUpDirector',
    feedback: 'detailed',
    finish_time: date,
    languages: 'py',
    score_mode: 'partial',
    penalty: 1,
    penalty_calc_policy: 'sum',
    penalty_type: 'contest_start',
    points_decay_factor: 0,
    problemset_id: 1,
    scoreboard: 100,
    show_penalty: true,
    default_show_all_contestants_in_scoreboard: false,
    show_scoreboard_after: true,
    start_time: date,
    submissions_gap: 1200,
    title: 'hello omegaUp',
  };

  const problemInfo: types.ProblemInfo = {
    alias: 'problemOmegaUp',
    accepts_submissions: true,
    karel_problem: false,
    commit: 'abc',
    languages: ['py3'],
    limits: {
      input_limit: '10 KiB',
      memory_limit: '32 MiB',
      overall_wall_time_limit: '1s',
      time_limit: '1s',
    },
    points: 100,
    problem_id: 1,
    problemsetter: {
      classname: 'user-rank-unranked',
      creation_date: date,
      name: 'omegaUp admin',
      username: 'omegaup',
    },
    quality_seal: false,
    sample_input: undefined,
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
    source: 'omegaUp classics',
    statement: {
      images: {},
      sources: {},
      language: 'es',
      markdown: '# test',
    },
    title: 'Triangulos',
    visibility: 2,
    input_limit: 1000,
  };

  const problems: types.NavbarProblemsetProblem[] = [
    {
      acceptsSubmissions: true,
      alias: 'problemOmegaUp',
      bestScore: 100,
      hasRuns: true,
      maxScore: 100,
      text: 'A. hello problem omegaUp',
    },
    {
      acceptsSubmissions: true,
      alias: 'otherProblemOmegaUp',
      bestScore: 100,
      hasRuns: true,
      maxScore: 100,
      text: 'B. hello other problem omegaUp',
    },
  ];

  it('Should handle details for a problem in a contest, practice mode', async () => {
    const wrapper = mount(arena_ContestPractice, {
      propsData: {
        contest,
        problems,
        problemInfo,
      },
    });

    expect(wrapper.find('.clock').text()).toBe('∞');
    expect(wrapper.find('.socket-status-error').text()).toBe('✗');
    expect(wrapper.find('a[data-problem=problemOmegaUp]').text()).toBe(
      'A. hello problem omegaUp',
    );
    expect(wrapper.find('a[data-problem=otherProblemOmegaUp]').text()).toBe(
      'B. hello other problem omegaUp',
    );
    expect(wrapper.text()).toContain(problemInfo.points);
    expect(wrapper.text()).toContain(time.formatDateLocal(date));

    await wrapper.find('a[data-problem=problemOmegaUp]').trigger('click');
    expect(wrapper.emitted('navigate-to-problem')).toBeDefined();
  });

  it('Should handle details for a run in a contest, practice mode', async () => {
    const run: types.Run = {
      alias: 'problemOmegaUp',
      classname: 'user-rank-unranked',
      contest_score: 100,
      country: 'xx',
      execution: 'EXECUTION_FINISHED',
      guid: '78099022574726af861839e1b4210188',
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
      type: 'normal',
      username: 'test_user_1',
      verdict: 'AC',
    };

    const wrapper = mount(arena_ContestPractice, {
      propsData: {
        contest,
        problems,
        problem: problems[0],
        runs: [run],
        problemInfo,
      },
    });

    await wrapper
      .find(`button[data-run-details="${run.guid}"]`)
      .trigger('click');
    expect(wrapper.emitted('show-run')).toEqual([
      [
        {
          guid: '78099022574726af861839e1b4210188',
          hash: '#problems/problemOmegaUp/show-run:78099022574726af861839e1b4210188',
          isAdmin: false,
        },
      ],
    ]);
  });
});
