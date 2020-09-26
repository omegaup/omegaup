import { mount } from '@vue/test-utils';
import expect from 'expect';
import { types } from '../../api_types';
import * as time from '../../time';

import problem_Details from './Details.vue';

describe('Details.vue', () => {
  const date = new Date();
  const sampleProblem = <types.ProblemInfo>{
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
      language: 'es',
      markdown: '# test',
    },
    title: 'Triangulos',
    visibility: 2,
    input_limit: 1000,
  };

  const user = <types.UserInfoForProblem>{
    admin: true,
    loggedIn: true,
    reviewer: true,
  };

  const nominationStatus = <types.NominationStatus>{
    alreadyReviewed: false,
    dismissed: false,
    dismissedBeforeAC: false,
    nominated: false,
    nominatedBeforeAC: false,
    solved: false,
    tried: false,
  };

  const histogram = <types.Histogram>{
    difficulty: 0.0,
    difficultyHistogram: undefined,
    quality: 0.0,
    qualityHistogram: undefined,
  };

  it('Should handle no nomination payload', () => {
    const wrapper = mount(problem_Details, {
      propsData: {
        problem: sampleProblem,
        user: user,
        nominationStatus: nominationStatus,
        initialClarifications: [],
        activeTab: 'problems',
        runs: <types.Run[]>[],
        allRuns: <types.Run[]>[],
        clarifications: <types.Clarification[]>[],
        solutionStatus: 'not_found',
        histogram: histogram,
      },
    });

    expect(wrapper.text()).toContain(sampleProblem.points);
    expect(wrapper.text()).toContain(time.formatDate(date));
  });
});
