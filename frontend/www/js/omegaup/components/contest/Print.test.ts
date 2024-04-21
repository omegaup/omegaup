import { mount } from '@vue/test-utils';
import type { types } from '../../api_types';

import T from '../../lang';

import contest_Print from './Print.vue';

describe('Print.vue', () => {
  const now = new Date();
  const problem: types.ProblemDetails = {
    accepted: 1,
    alias: 'triangulos',
    allow_user_add_tags: false,
    accepts_submissions: true,
    creation_date: now,
    email_clarifications: false,
    order: 'asc',
    show_diff: 'none',
    score: 1,
    submissions: 1,
    version: 'abcdef1212',
    visits: 1,
    nominationStatus: {
      alreadyReviewed: false,
      canNominateProblem: true,
      dismissed: false,
      dismissedBeforeAc: false,
      language: 'py2',
      nominated: false,
      nominatedBeforeAc: false,
      solved: true,
      tried: true,
    },
    karel_problem: false,
    commit: 'abc',
    languages: ['py3'],
    letter: 'a',
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
      creation_date: now,
      name: 'omegaUp admin',
      username: 'omegaup',
    },
    quality_seal: false,
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
  const problems: types.ProblemDetails[] = [
    problem,
    { ...problem, ...{ alias: 'sumas', title: 'sumas', letter: 'b' } },
  ];

  it('Should handle details for a contest in print mode', () => {
    const contestTitle = 'hello contest';
    const wrapper = mount(contest_Print, {
      propsData: { contestTitle, problems },
    });

    const printIcon = wrapper.find('svg[data-prefix="fas"]');
    expect(printIcon.text()).toContain(T.contestAndProblemPrintButtonDesc);
    printIcon.trigger('click');
    expect(wrapper.emitted('print-page')).toBeDefined();

    expect(
      wrapper.find('h3[data-problem-title="triangulos"]').text(),
    ).toContain(contestTitle);
    expect(
      wrapper.find('h3[data-problem-title="triangulos"]').text(),
    ).toContain('Triangulos');
    expect(wrapper.find('h3[data-problem-title="sumas"]').text()).toContain(
      contestTitle,
    );
    expect(wrapper.find('h3[data-problem-title="sumas"]').text()).toContain(
      'sumas',
    );
  });
});
