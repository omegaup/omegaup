import { mount } from '@vue/test-utils';
import type { types } from '../../api_types';

import T from '../../lang';

import problem_Print from './Print.vue';

describe('Print.vue', () => {
  const problem: types.ProblemInfo = {
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
      creation_date: new Date(Date.now()),
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
      language: 'en',
      markdown: `# test with embed code
Here we can add code.
<details>
  <summary>
    Example:
  </summary>

  {{sample.cpp}}

  </details>
      `,
    },
    title: 'Triangulos',
    visibility: 2,
    input_limit: 1000,
  };

  it('Should handle details for a problem in print mode', () => {
    const wrapper = mount(problem_Print, {
      propsData: {
        problem,
      },
    });

    const printIcon = wrapper.find('svg[data-prefix="fas"]');
    expect(printIcon.text()).toContain(T.contestAndProblemPrintButtonDesc);
    printIcon.trigger('click');
    expect(wrapper.emitted('print-page')).toBeDefined();

    expect(wrapper.find('h3[data-problem-title]').text()).toBe(problem.title);
  });
});
