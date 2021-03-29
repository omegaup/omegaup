jest.mock('../../../third_party/js/diff_match_patch.js');

import Vue from 'vue';
import arena_ContestPractice from '../components/arena/ContestPractice.vue';
import { types } from '../api_types';
import { navigateToProblem, setLocationHash } from './navigation';
import { PopupDisplayed } from '../components/problem/Details.vue';
import { ActiveProblem } from '../components/arena/ContestPractice.vue';
import { mutations } from './problemStore';

const vueInstance: Vue & {
  problemInfo: types.ProblemInfo | null;
  popupDisplayed?: PopupDisplayed;
  problem: ActiveProblem | null;
} = new Vue({
  components: {
    'omegaup-arena-contest-practice': arena_ContestPractice,
  },
  render: function (createElement) {
    return createElement('omegaup-badge-details', {
      props: {
        problemInfo: null,
        problem: null,
      },
    });
  },
});
vueInstance.problemInfo = {
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
  points: 100,
  preferred_language: 'kp',
  problem_id: 1,
  quality_seal: true,
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
vueInstance.popupDisplayed = PopupDisplayed.RunSubmit;
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
setLocationHash('#problems');

const { addProblem } = mutations;

describe('navigation.ts', () => {
  describe('navigateToProblem', () => {
    it('Should change window location hash', async () => {
      const params = {
        target: vueInstance,
        runs: [],
        problems: navbarProblems,
        problem: navbarProblems[0],
      };
      const state = { problems: {} };
      if (vueInstance.problemInfo) {
        addProblem(state, vueInstance.problemInfo);
      }
      navigateToProblem(params);
      const getLocationHash = jest
        .fn()
        .mockReturnValue('#problems/problem_alias/new-run');

      expect(getLocationHash()).toEqual(
        `#problems/${navbarProblems[0].alias}/new-run`,
      );
    });
  });
});
