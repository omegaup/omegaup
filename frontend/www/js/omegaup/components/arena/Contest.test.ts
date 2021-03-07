jest.mock('../../../../third_party/js/diff_match_patch.js');

import { mount } from '@vue/test-utils';
import type { types } from '../../api_types';
import * as time from '../../time';

import arena_Contest from './Contest.vue';

describe('Contest.vue', () => {
  const date = new Date();

  const contestDetails: types.ContestPublicDetails = {
    admission_mode: 'public',
    alias: 'omegaUp',
    description: 'hello omegaUp',
    director: 'omegaUpDirector',
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
  };

  const sampleProblem: types.ProblemInfo = {
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
  };

  it('Should handle details for a problem in a contest', async () => {
    const wrapper = mount(arena_Contest, {
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

    expect(wrapper.find('.clock').text()).toBe('00:00:00');
    expect(wrapper.find('.socket-status-ok').text()).toBe('•');
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
