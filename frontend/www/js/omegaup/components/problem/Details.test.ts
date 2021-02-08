jest.mock('../../../../third_party/js/diff_match_patch.js');

import { mount } from '@vue/test-utils';
import type { types } from '../../api_types';
import * as time from '../../time';

import problem_Details from './Details.vue';

describe('Details.vue', () => {
  const date = new Date();
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
    feedback: 'none',
    guid: '80bbe93bc01c1d47ff9fb396dfaff741',
    judged_by: '',
    language: 'py3',
    logs: '',
    show_diff: 'none',
    source: 'print(3)',
    source_link: false,
    source_name: 'Main.py3',
    source_url: 'blob:http://localhost:8001/url',
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
      guid: 'abcdefg',
      language: 'py3',
      memory: 0,
      penalty: 0,
      runtime: 0,
      score: 1,
      status: 'ready',
      submit_delay: 0,
      time: new Date(),
      username: 'omegaUp',
      verdict: 'AC',
    },
  ];

  it('Should handle details for a problem', () => {
    const wrapper = mount(problem_Details, {
      propsData: {
        initialTab: 'problems',
        problem: sampleProblem,
        runDetailsData,
        user: user,
        nominationStatus: nominationStatus,
        initialClarifications: [],
        activeTab: 'problems',
        runs: [] as types.Run[],
        allRuns: [] as types.Run[],
        clarifications: [] as types.Clarification[],
        solutionStatus: 'not_found',
        histogram: histogram,
        showNewRunWindow: false,
        publicTags: [],
      },
    });

    expect(wrapper.text()).toContain(sampleProblem.points);
    expect(wrapper.text()).toContain(time.formatDate(date));
  });

  it('Should handle run details for a problem', async () => {
    const wrapper = mount(problem_Details, {
      propsData: {
        initialTab: 'problems',
        problem: sampleProblem,
        runDetailsData,
        user: user,
        nominationStatus: nominationStatus,
        initialClarifications: [],
        activeTab: 'problems',
        runs: runs,
        allRuns: runs,
        clarifications: [] as types.Clarification[],
        solutionStatus: 'not_found',
        histogram: histogram,
        showNewRunWindow: false,
        publicTags: [],
        shouldShowTabs: true,
      },
    });

    await wrapper.find('a[href="#runs"]').trigger('click');
    await wrapper.find('td div.dropdown>button.btn-secondary').trigger('click');
    await wrapper
      .find(
        '.tab-content .show table tbody tr td div.dropdown ul li[data-actions-details] button',
      )
      .trigger('click');
    expect(
      wrapper.find('.tab-content .show div[data-overlay]').html(),
    ).toBeTruthy();
  });

  it('Should handle run actions for a run in a given problem', async () => {
    const wrapper = mount(problem_Details, {
      propsData: {
        initialTab: 'problems',
        problem: sampleProblem,
        runDetailsData,
        user: user,
        nominationStatus: nominationStatus,
        initialClarifications: [],
        activeTab: 'problems',
        runs: runs,
        allRuns: runs,
        clarifications: [] as types.Clarification[],
        solutionStatus: 'not_found',
        histogram: histogram,
        showNewRunWindow: false,
        publicTags: [],
        shouldShowTabs: true,
      },
    });

    await wrapper.find('a[href="#runs"]').trigger('click');
    await wrapper.find('td div.dropdown>button.btn-secondary').trigger('click');
    await wrapper
      .find(
        '.tab-content .show table tbody tr td div.dropdown ul li[data-actions-rejudge] button',
      )
      .trigger('click');
    expect(wrapper.emitted('rejudge')).toBeDefined();

    await wrapper.find('td div.dropdown>button.btn-secondary').trigger('click');
    await wrapper
      .find(
        '.tab-content .show table tbody tr td div.dropdown ul li[data-actions-disqualify] button',
      )
      .trigger('click');
    expect(wrapper.emitted('disqualify')).toBeDefined();
  });
});
