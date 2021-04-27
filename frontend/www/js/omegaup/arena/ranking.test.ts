jest.mock('../../../third_party/js/diff_match_patch.js');

import { types } from '../api_types';
import { Ranking } from './ranking';

describe('ranking', () => {
  const scoreboard: types.Scoreboard = {
    problems: [
      {
        alias: 'problem_alias',
        order: 1,
      },
      {
        alias: 'problem_alias_2',
        order: 2,
      },
    ],
    ranking: [
      {
        classname: 'user-rank-unranked',
        username: 'omegaUp',
        country: 'MX',
        is_invited: true,
        problems: [
          {
            alias: 'problem_alias',
            points: 100,
            runs: 1,
            percent: 0,
            penalty: 3,
          },
          {
            alias: 'problem_alias_2',
            points: 100,
            runs: 3,
            percent: 0,
            penalty: 5,
          },
        ],
        total: {
          points: 0,
          penalty: 0,
        },
      },
    ],
    start_time: new Date(),
    time: new Date(),
    title: 'contest',
  };
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

  describe('updateProblemScore', () => {
    it('Should update problem score in a contest', () => {
      const arenaRanking = new Ranking({
        startTime: null,
        finishTime: null,
      });
      const params = {
        alias: 'problem_alias',
        previousScore: 100,
        username: 'omegaUp',
        scoreboard,
      };

      arenaRanking.updateProblemScore(params);
      expect(arenaRanking.scoreboardRanking).toEqual([
        {
          classname: 'user-rank-unranked',
          country: 'MX',
          is_invited: true,
          problems: [
            {
              alias: 'problem_alias',
              penalty: 3,
              percent: 0,
              points: 100,
              runs: 1,
            },
            {
              alias: 'problem_alias_2',
              penalty: 5,
              percent: 0,
              points: 100,
              runs: 3,
            },
          ],
          total: {
            penalty: 0,
            points: 200,
          },
          username: 'omegaUp',
        },
      ]);
    });

    it('Should update problem when ranking change', () => {
      const arenaRanking = new Ranking({
        startTime: null,
        finishTime: null,
      });
      const params = {
        currentUsername: 'omegaUp',
        scoreboard: scoreboard,
        navbarProblems: navbarProblems,
      };
      arenaRanking.onRankingChanged(params);
      expect(arenaRanking.scoreboardRanking[0].total.points).toEqual(200);
      expect(arenaRanking.miniRankingUsers[0].position).toEqual(0);
    });
  });
});
