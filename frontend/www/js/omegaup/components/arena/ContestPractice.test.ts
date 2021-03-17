jest.mock('../../../../third_party/js/diff_match_patch.js');

import { mount } from '@vue/test-utils';
import type { types } from '../../api_types';
import * as time from '../../time';

import arena_ContestPractice from './ContestPractice.vue';

describe('ContestPractice.vue', () => {
  const date = new Date();

  const contestDetails = {
    admission_mode: 'public',
    alias: 'omegaUp',
    description: 'hello omegaUp',
    feedback: 'detailed',
    finish_time: date,
    languages: 'py',
    partial_score: true,
    penalty: 1,
    penalty_calc_policy: 'sum',
    penalty_type: 'contest_start',
    points_decay_factor: 0,
    problemset_id: 1,
    rerun_id: 0,
    scoreboard: 100,
    show_penalty: true,
    show_scoreboard_after: true,
    start_time: date,
    submissions_gap: 1200,
    title: 'hello omegaUp',
  } as types.ContestPublicDetails;

  const sampleProblem = {
    alias: 'triangulos',
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
    guid: '80bbe93bc01c1d47ff9fb396dfaff741',
    runDetailsData: {
      admin: false,
      alias: 'sumas',
      cases: {},
      details: {
        compile_meta: {
          Main: {
            memory: 12091392,
            sys_time: 0.029124,
            time: 0.174746,
            verdict: 'OK',
            wall_time: 0.51659,
          },
        },
        contest_score: 5,
        groups: [],
        judged_by: 'localhost',
        max_score: 100,
        memory: 10407936,
        score: 0.05,
        time: 0.31891,
        verdict: 'PA',
        wall_time: 0.699709,
      },
      groups: [],
      guid: '80bbe93bc01c1d47ff9fb396dfaff741',
      judged_by: '',
      language: 'py3',
      logs: '',
      show_diff: 'none',
      source: 'print(3)',
      source_link: false,
      source_name: 'Main.py3',
      source_url: 'blob:http://localhost:8001/url',
    } as types.RunDetails,
  } as types.ProblemInfo;

  it('Should handle details for a problem in a contest, practice mode', async () => {
    const wrapper = mount(arena_ContestPractice, {
      propsData: {
        contest: contestDetails,
        problems: [
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
        ] as types.NavbarProblemsetProblem[],
        problemInfo: sampleProblem,
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
    expect(wrapper.text()).toContain(sampleProblem.points);
    expect(wrapper.text()).toContain(time.formatDateLocal(date));

    await wrapper.find('a[data-problem=problemOmegaUp]').trigger('click');
    expect(wrapper.emitted('navigate-to-problem')).toBeDefined();
  });
});
