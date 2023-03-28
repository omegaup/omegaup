jest.mock('../../../third_party/js/diff_match_patch.js');
jest.mock('../location');

import Vue from 'vue';
import arena_ContestPractice from '../components/arena/ContestPractice.vue';
import { types } from '../api_types';
import { setLocationHash } from '../location';
import {
  NavigationRequest,
  NavigationType,
  navigateToProblem,
  getScoreModeEnum,
} from './navigation';
import { PopupDisplayed } from '../components/problem/Details.vue';
import { storeConfig } from './problemStore';
import { createLocalVue } from '@vue/test-utils';
import Vuex from 'vuex';
import fetchMock from 'jest-fetch-mock';
import { OmegaUp } from '../omegaup';

const run: types.RunWithDetails = {
  alias: 'problem_alias',
  classname: 'user-rank-unranked',
  country: 'MX',
  guid: 'abcdef',
  language: 'py2',
  memory: 1000,
  penalty: 60,
  runtime: 60,
  score: 0.6,
  score_by_group: { easy: 0.2, medium: 0.3, hard: 0.1 },
  status: 'ready',
  submit_delay: 1,
  time: new Date(0),
  type: 'normal',
  username: 'omegaup',
  verdict: 'PA',
};

const runs: types.RunWithDetails[] = [
  run,
  {
    ...run,
    guid: 'ffeedd',
    score: 0.3,
    score_by_group: { easy: 0.0, medium: 0.1, hard: 0.2 },
  },
  {
    ...run,
    guid: 'a1b2c3',
    score_by_group: { easy: 0.3, medium: 0.3, hard: 0.0 },
  },
];

const problemDetails: types.ProblemDetails = {
  accepted: 1,
  allow_user_add_tags: true,
  creation_date: new Date(),
  email_clarifications: true,
  nominationStatus: {
    alreadyReviewed: true,
    canNominateProblem: true,
    dismissed: true,
    dismissedBeforeAc: true,
    language: 'py3',
    nominated: true,
    nominatedBeforeAc: true,
    solved: true,
    tried: true,
  },
  accepts_submissions: true,
  commit: 'abcdef',
  alias: 'problem_alias',
  input_limit: 10240,
  karel_problem: true,
  languages: ['kp', 'kj'],
  letter: 'A',
  limits: {
    input_limit: '10Kibs',
    memory_limit: '10Kibs',
    overall_wall_time_limit: '1s',
    time_limit: '1s',
  },
  order: 'asc',
  score: 100,
  show_diff: 'no',
  submissions: 1,
  visits: 1,
  version: '',
  points: 100,
  preferred_language: 'kp',
  problem_id: 1,
  quality_seal: true,
  runs,
  settings: {
    cases: { easy: { in: '2', out: '4', weight: 1 } },
    limits: {
      ExtraWallTime: '1s',
      OutputLimit: '10Kibs',
      MemoryLimit: '10Kibs',
      OverallWallTimeLimit: '1s',
      TimeLimit: '1s',
    },
    validator: {
      name: 'validator',
    },
  },
  source: 'omegaUp classics',
  statement: {
    images: {},
    sources: {},
    language: 'kj',
    markdown: 'some markdown',
  },
  title: 'A. Problem',
  visibility: 2,
};

OmegaUp.username = 'omegaup';

const vueInstance: Vue & {
  problemInfo: types.ProblemInfo;
  popupDisplayed?: PopupDisplayed;
  problem: types.NavbarProblemsetProblem | null;
} = new Vue({
  components: {
    'omegaup-arena-contest-practice': arena_ContestPractice,
  },
  render: function (createElement) {
    return createElement('omegaup-badge-details', {
      props: {
        problemInfo: problemDetails,
        problem: null,
      },
    });
  },
});
vueInstance.problemInfo = problemDetails;
const navbarProblems: types.NavbarProblemsetProblem[] = [
  {
    acceptsSubmissions: true,
    alias: 'problem_alias',
    bestScore: 100,
    hasRuns: true,
    maxScore: 100,
    text: 'A. Problem',
  },
  {
    acceptsSubmissions: true,
    alias: 'problem_alias_2',
    bestScore: 80,
    hasRuns: true,
    maxScore: 100,
    text: 'B. Problem 2',
  },
];

describe('navigation.ts', () => {
  describe('navigateToProblem', () => {
    beforeEach(() => {
      fetchMock.enableMocks();
      fetchMock.mockIf(/^\/api\/.*/, (req: Request) => {
        if (req.url != '/api/problem/details/') {
          return Promise.resolve({
            ok: false,
            status: 404,
            body: JSON.stringify({
              status: 'error',
              error: `Invalid call to "${req.url}" in test`,
              errorcode: 403,
            }),
          });
        }
        return Promise.resolve({
          status: 200,
          body: JSON.stringify({
            ...problemDetails,
            status: 'ok',
          }),
        });
      });
    });

    it('Should change hash when contest alias is declared in practice mode', async () => {
      vueInstance.popupDisplayed = PopupDisplayed.None;
      const params: NavigationRequest = {
        type: NavigationType.ForContest,
        target: vueInstance,
        problems: navbarProblems,
        problem: navbarProblems[0],
        contestAlias: 'contest_alias',
        contestMode: getScoreModeEnum('partial'),
      };
      await navigateToProblem(params);
      expect(setLocationHash).toHaveBeenCalledWith(
        `#problems/${params.problem.alias}`,
      );
    });

    it('Should change window location hash when problem was stored in vuex', async () => {
      vueInstance.popupDisplayed = PopupDisplayed.RunSubmit;
      const params: NavigationRequest = {
        type: NavigationType.ForContest,
        target: vueInstance,
        problems: navbarProblems,
        problem: navbarProblems[0],
        contestAlias: 'contest_alias',
        contestMode: getScoreModeEnum('partial'),
      };
      const localVue = createLocalVue();
      localVue.use(Vuex);
      new Vuex.Store(storeConfig);
      await navigateToProblem(params);
      expect(setLocationHash).toHaveBeenCalledWith(
        `#problems/${params.problem.alias}/new-run`,
      );
    });

    it('Should get the best score for contest with subtasks', async () => {
      vueInstance.popupDisplayed = PopupDisplayed.RunSubmit;
      const params: NavigationRequest = {
        type: NavigationType.ForContest,
        target: vueInstance,
        problems: navbarProblems,
        problem: navbarProblems[1],
        contestAlias: 'contest_alias',
        contestMode: getScoreModeEnum('max_per_group'),
      };
      const localVue = createLocalVue();
      localVue.use(Vuex);
      new Vuex.Store(storeConfig);
      await navigateToProblem(params);
      expect(setLocationHash).toHaveBeenCalledWith(
        `#problems/${params.problem.alias}/new-run`,
      );
      expect(vueInstance.problem).not.toBeNull();
      expect(vueInstance.problem?.bestScore).toBe(0.8);
    });
  });
});
