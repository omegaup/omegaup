jest.mock('../../../../third_party/js/diff_match_patch.js');

import { mount } from '@vue/test-utils';
import type { types } from '../../api_types';
import T from '../../lang';
import * as time from '../../time';

import problem_Details from './Details.vue';
import arena_EphemeralGrader from '../arena/EphemeralGrader.vue';
import { DisqualificationType } from '../arena/Runs.vue';

describe('Details.vue', () => {
  const date = new Date();
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

  const runDetailsData: types.RunDetails = {
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
    guid: '80bbe93bc01c1d47ff9fb396dfaff741',
    judged_by: '',
    language: 'py3',
    logs: '',
    show_diff: 'none',
    source: 'print(3)',
    source_link: false,
    source_name: 'Main.py3',
    source_url: 'blob:http://localhost:8001/url',
    feedback: [],
  };

  const user: types.UserInfoForProblem = {
    admin: true,
    loggedIn: true,
    reviewer: true,
  };

  const nominationStatus: types.NominationStatus = {
    alreadyReviewed: false,
    canNominateProblem: false,
    language: 'en',
    dismissed: false,
    dismissedBeforeAc: false,
    nominated: false,
    nominatedBeforeAc: false,
    solved: false,
    tried: false,
  };

  const histogram: types.Histogram = {
    difficulty: 0.0,
    difficultyHistogram: '[0,1,2,3,4]',
    quality: 0.0,
    qualityHistogram: '[0,1,2,3,4]',
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
      type: 'normal',
    },
  ];

  it('Should handle details for a problem', () => {
    const wrapper = mount(problem_Details, {
      propsData: {
        initialTab: 'problems',
        problem,
        runDetailsData,
        user,
        nominationStatus,
        activeTab: 'problems',
        runs: [] as types.Run[],
        allRuns: [] as types.Run[],
        clarifications: [] as types.Clarification[],
        histogram,
        showNewRunWindow: false,
        publicTags: [],
      },
    });

    expect(wrapper.text()).toContain(problem.points);
    expect(wrapper.text()).toContain(time.formatDate(date));
  });

  it('Should handle run details for a problem', async () => {
    const wrapper = mount(problem_Details, {
      propsData: {
        initialTab: 'problems',
        problem,
        runDetailsData,
        user,
        nominationStatus,
        activeTab: 'problems',
        runs,
        allRuns: runs,
        clarifications: [] as types.Clarification[],
        histogram,
        showNewRunWindow: false,
        publicTags: [],
        shouldShowTabs: true,
      },
    });

    await wrapper.find('a[href="#runs"]').trigger('click');
    await wrapper.find('td div.dropdown>button.btn-secondary').trigger('click');
    await wrapper.find('button[data-run-details]').trigger('click');
    expect(
      wrapper.find('.tab-content .show div[data-overlay]').html(),
    ).toBeTruthy();
  });

  it('Should handle run actions for a run in a given problem', async () => {
    const wrapper = mount(problem_Details, {
      propsData: {
        initialTab: 'problems',
        problem,
        problemAlias: problem.alias,
        isAdmin: true,
        runDetailsData,
        user,
        nominationStatus,
        activeTab: 'problems',
        runs,
        allRuns: runs,
        clarifications: [] as types.Clarification[],
        histogram,
        showNewRunWindow: false,
        publicTags: [],
        shouldShowTabs: true,
      },
    });

    await wrapper.find('a[href="#runs"]').trigger('click');
    await wrapper.find('td div.dropdown>button.btn-secondary').trigger('click');
    await wrapper.find('button[data-actions-rejudge]').trigger('click');
    expect(wrapper.emitted('rejudge')).toEqual([
      [
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
          status_memory: 'MEMORY_AVAILABLE',
          status_runtime: 'RUNTIME_AVAILABLE',
          status: 'ready',
          submit_delay: 0,
          time: expect.any(Date),
          username: 'omegaUp',
          verdict: 'AC',
          type: 'normal',
        },
      ],
    ]);

    await wrapper.find('td div.dropdown>button.btn-secondary').trigger('click');
    await wrapper.find('button[data-actions-disqualify]').trigger('click');
    expect(wrapper.emitted('disqualify')).toEqual([
      [
        {
          disqualificationType: DisqualificationType.ByGUID,
          run: {
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
            time: expect.any(Date),
            username: 'omegaUp',
            verdict: 'AC',
            type: 'normal',
          },
        },
      ],
    ]);

    await wrapper.find('td div.dropdown>button.btn-secondary').trigger('click');
    await wrapper.find('button[data-run-details]').trigger('click');
    expect(wrapper.emitted('show-run')).toEqual([
      [
        {
          guid: 'abcdefg',
          hash: '#runs/triangulos/show-run:abcdefg',
          isAdmin: true,
        },
      ],
    ]);
  });

  it('Should handle problem clarifications', async () => {
    const clarifications: types.Clarification[] = [
      {
        answer: undefined,
        author: 'omegaUp',
        clarification_id: 1,
        contest_alias: 'Concurso de prueba',
        message: 'Clarificación de prueba 1',
        problem_alias: 'Problema de prueba',
        public: true,
        receiver: undefined,
        time: new Date(),
      },
      {
        answer: 'Ok',
        author: 'omegaUp',
        clarification_id: 2,
        contest_alias: undefined,
        message: 'Clarificación de prueba 2',
        problem_alias: 'Problema de prueba',
        public: false,
        receiver: undefined,
        time: new Date(),
      },
    ];
    const wrapper = mount(problem_Details, {
      propsData: {
        initialTab: 'problems',
        problem,
        runDetailsData,
        user,
        nominationStatus,
        activeTab: 'problems',
        runs,
        allRuns: runs,
        clarifications: clarifications,
        histogram,
        showNewRunWindow: false,
        publicTags: [],
        shouldShowTabs: true,
      },
    });
    await wrapper.find('a[href="#clarifications"]').trigger('click');
    expect(wrapper.find('.tab-content .show table thead tr th').text()).toBe(
      T.clarificationInfo,
    );
  });

  it('Should handle unrecognized source filename error', () => {
    const wrapper = mount(problem_Details, {
      propsData: {
        initialTab: 'problems',
        problem,
        runDetailsData,
        user,
        nominationStatus,
        activeTab: 'problems',
        runs,
        allRuns: runs,
        clarifications: [] as types.Clarification[],
        histogram,
        showNewRunWindow: false,
        publicTags: [],
        shouldShowTabs: true,
      },
    });

    expect(wrapper.find('div[data-markdown-statement]').text()).toContain(
      'Unrecognized source filename: sample.cpp',
    );
  });

  it('Should handle a valid source filename with content', async () => {
    const wrapper = mount(problem_Details, {
      propsData: {
        initialTab: 'problems',
        problem: {
          ...problem,
          statement: {
            ...problem.statement,
            sources: {
              'sample.cpp': `#include <iostream>

int main() {
  std::cout << "This is only an example";
  return 0;
}`,
            },
          },
        },
        runDetailsData,
        user,
        nominationStatus,
        activeTab: 'problems',
        runs,
        allRuns: runs,
        clarifications: [] as types.Clarification[],
        histogram,
        showNewRunWindow: false,
        publicTags: [],
        shouldShowTabs: true,
      },
    });

    expect(wrapper.find('details').attributes()).toMatchObject({});
    await wrapper.find('details > summary').trigger('click');
    expect(wrapper.find('details').attributes()).toMatchObject({ open: '' });
    expect(wrapper.find('div[data-markdown-statement]').text()).toContain(
      '#include <iostream>',
    );
    expect(wrapper.find('div[data-markdown-statement]').text()).toContain(
      'This is only an example',
    );
  });

  // TODO: Unskip when the component is visible
  it.skip('Should show the ephemeral grader for regular problems', async () => {
    const wrapper = mount(problem_Details, {
      propsData: {
        initialTab: 'problems',
        problem,
        runDetailsData,
        user,
        nominationStatus,
        activeTab: 'problems',
        runs,
        allRuns: runs,
        clarifications: [] as types.Clarification[],
        histogram,
        showNewRunWindow: false,
        publicTags: [],
        shouldShowTabs: true,
      },
    });

    expect(wrapper.findComponent(arena_EphemeralGrader).exists()).toBe(true);
  });

  it('Should hide the ephemeral grader for Karel problems', async () => {
    const wrapper = mount(problem_Details, {
      propsData: {
        initialTab: 'problems',
        problem: {
          ...problem,
          karel_problem: true,
          languages: ['kp', 'kj'],
        },
        runDetailsData,
        user,
        nominationStatus,
        activeTab: 'problems',
        runs,
        allRuns: runs,
        clarifications: [] as types.Clarification[],
        histogram,
        showNewRunWindow: false,
        publicTags: [],
        shouldShowTabs: true,
      },
    });

    expect(wrapper.findComponent(arena_EphemeralGrader).exists()).toBe(false);
  });
});
