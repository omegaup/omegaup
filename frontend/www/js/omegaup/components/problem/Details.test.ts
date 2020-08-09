import { shallowMount } from '@vue/test-utils';
import expect from 'expect';
import * as time from '../../time';

import problem_Details from './Details.vue';

describe('Details.vue', () => {
  const date = new Date();
  const sampleProblem = {
    alias: 'triangulos',
    karel_problem: false,
    limits: {
      input_limit: '10 KiB',
      memory_limit: '32 MiB',
      overall_wall_time_limit: '1s',
      time_limit: '1s',
    },
    points: 100,
    problemsetter: {
      classname: 'user-rank-unranked',
      creation_date: date,
      name: 'omegaUp admin',
      username: 'omegaup',
    },
    quality_seal: false,
    sample_input: null,
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
      images: [],
      language: 'es',
      markdown: '# test',
    },
    title: 'Triangulos',
    visibility: 2,
  };

  const user = {
    admin: true,
    loggedIn: true,
    reviewer: true,
  };

  it('Should handle no nomination payload', async () => {
    const wrapper = shallowMount(problem_Details, {
      propsData: {
        problem: sampleProblem,
        user,
      },
    });

    expect(wrapper.text()).toContain(time.formatDate(date));
  });
});
