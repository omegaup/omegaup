jest.mock('../../../third_party/js/diff_match_patch.js');

import { types } from '../api_types';
import T from '../lang';
import {
  onRankingChanged,
  onRankingEvents,
  updateProblemScore,
  createChart,
  scoreboardColors,
} from './ranking';

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
            runs: 1,
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
  const scoreboardEvents: types.ScoreboardEvent[] = [
    {
      classname: 'user-rank-unranked',
      username: 'omegaUp',
      country: 'MX',
      delta: 7,
      is_invited: true,
      problem: {
        alias: 'problem_alias',
        points: 100,
        penalty: 3,
      },
      total: {
        points: 100,
        penalty: 3,
      },
    },
    {
      classname: 'user-rank-unranked',
      username: 'omegaUp',
      country: 'MX',
      delta: 7.5,
      is_invited: true,
      problem: {
        alias: 'problem_alias_2',
        points: 100,
        penalty: 5,
      },
      total: {
        points: 100,
        penalty: 5,
      },
    },
  ];
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
      const params = {
        alias: 'problem_alias',
        previousScore: 100,
        username: 'omegaUp',
        scoreboard,
      };
      const scoreboardRanking = updateProblemScore(params);
      expect(scoreboardRanking).toEqual([
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
              runs: 1,
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
      const params = {
        currentUsername: 'omegaUp',
        scoreboard: scoreboard,
        navbarProblems: navbarProblems,
      };
      const { ranking, users } = onRankingChanged(params);
      expect(ranking[0].total.points).toEqual(200);
      expect(users[0].position).toEqual(0);
    });

    it('Should get ranking events for charts', () => {
      const { currentRanking, maxPoints } = onRankingChanged({
        currentUsername: 'omegaUp',
        scoreboard: scoreboard,
        navbarProblems: navbarProblems,
      });
      const params = {
        events: scoreboardEvents,
        currentRanking,
        maxPoints,
        startTimestamp: Date.now() - 10000,
        finishTimestamp: Date.now() + 10000,
      };
      const { series, navigatorData } = onRankingEvents(params);
      expect(navigatorData).toEqual([
        [expect.any(Number), 0],
        [expect.any(Number), 100],
        [expect.any(Number), 100],
      ]);
      expect(series).toEqual([
        expect.objectContaining({
          name: 'omegaUp',
          rank: 0,
          step: 'right',
          type: 'line',
        }),
      ]);
    });

    it('Should get ranking chart options object', () => {
      const startTimestamp = Date.now() - 10000;
      const finishTimestamp = Date.now() + 10000;
      const { currentRanking, maxPoints } = onRankingChanged({
        currentUsername: 'omegaUp',
        scoreboard: scoreboard,
        navbarProblems: navbarProblems,
      });
      const params = {
        events: scoreboardEvents,
        currentRanking,
        maxPoints,
        startTimestamp,
        finishTimestamp,
      };

      const { series, navigatorData } = onRankingEvents(params);
      expect(navigatorData).toEqual([
        [expect.any(Number), 0],
        [expect.any(Number), 100],
        [expect.any(Number), 100],
      ]);
      expect(series).toEqual([
        expect.objectContaining({
          name: 'omegaUp',
          rank: 0,
          step: 'right',
          type: 'line',
        }),
      ]);

      const rankingChartOptions = createChart({
        series,
        navigatorData,
        startTimestamp,
        finishTimestamp,
        maxPoints,
      });
      expect(rankingChartOptions).toEqual(
        expect.objectContaining({
          chart: {
            height: 300,
            spacingTop: 20,
          },
          colors: scoreboardColors,
          navigator: {
            series: {
              data: [
                [expect.any(Number), 0],
                [expect.any(Number), 100],
                [expect.any(Number), 100],
              ],
              lineColor: '#333',
              lineWidth: 3,
              step: 'left',
              type: 'line',
            },
          },
          plotOptions: {
            series: {
              animation: false,
              lineWidth: 3,
              marker: {
                lineWidth: 1,
                radius: 5,
                symbol: 'circle',
              },
              states: {
                hover: {
                  lineWidth: 3,
                },
              },
            },
          },
          rangeSelector: {
            enabled: false,
          },
          series: [
            {
              data: [
                [expect.any(Number), 0],
                [expect.any(Number), 100],
                [expect.any(Number), 100],
                [expect.any(Number), 100],
              ],
              name: 'omegaUp',
              rank: 0,
              step: 'right',
              type: 'line',
            },
          ],
          title: {
            text: T.wordsSubmissions,
          },
          xAxis: {
            max: expect.any(Number),
            min: expect.any(Number),
            ordinal: false,
            type: 'datetime',
          },
          yAxis: {
            max: 200,
            min: 0,
            showFirstLabel: false,
            showLastLabel: true,
            title: {
              text: T.wordsSubmissions,
            },
          },
        }),
      );
    });
  });
});
