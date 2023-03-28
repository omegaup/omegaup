jest.mock('../../../third_party/js/diff_match_patch.js');

import { types } from '../api_types';
import T from '../lang';
import {
  onRankingChanged,
  onRankingEvents,
  updateProblemScore,
  createChart,
  scoreboardColors,
  mergeRankings,
  onVirtualRankingChanged,
} from './ranking';
import { rankingStoreConfig } from './rankingStore';
import { createLocalVue } from '@vue/test-utils';
import Vuex from 'vuex';
import { ScoreMode } from './navigation';

describe('ranking', () => {
  const now = Date.now();
  let dateNowSpy: jest.SpyInstance<number, []> | null = null;

  beforeEach(() => {
    dateNowSpy = jest.spyOn(Date, 'now').mockImplementation(() => now);
    jest.useFakeTimers();
  });

  afterEach(() => {
    jest.runOnlyPendingTimers();
    jest.useRealTimers();
    if (dateNowSpy) {
      dateNowSpy.mockRestore();
    }
  });

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
  const originalScoreboardEvents: types.ScoreboardEvent[] = [
    {
      classname: 'user-rank-unranked',
      username: 'omegaUp_virtual',
      name: 'omegaUp [virtual]',
      country: 'MX',
      delta: 0.0001,
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
      username: 'omegaUp_virtual',
      name: 'omegaUp [virtual]',
      country: 'MX',
      delta: 1,
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

  describe('mergeRankings', () => {
    it('Should merge original ranking with current scoreboard', () => {
      const { mergedScoreboard, originalContestEvents } = mergeRankings({
        scoreboard,
        originalScoreboardEvents,
        navbarProblems,
      });
      expect(originalContestEvents).toEqual([
        {
          classname: 'user-rank-unranked',
          country: 'MX',
          delta: 0.0001,
          is_invited: true,
          name: 'omegaUp [virtual]',
          problem: { alias: 'problem_alias', penalty: 3, points: 100 },
          total: { penalty: 3, points: 100 },
          username: 'omegaUp_virtual',
        },
      ]);
      expect(mergedScoreboard).toEqual({
        problems: [
          { alias: 'problem_alias', order: 1 },
          { alias: 'problem_alias_2', order: 2 },
        ],
        ranking: [
          {
            classname: 'user-rank-unranked',
            country: 'MX',
            is_invited: true,
            name: 'omegaUp [virtual]',
            place: 1,
            problems: [
              {
                alias: 'problem_alias',
                penalty: 3,
                percent: 100,
                points: 100,
                runs: 1,
              },
              {
                alias: 'problem_alias_2',
                penalty: 0,
                percent: 0,
                points: 0,
                runs: 0,
              },
            ],
            total: {
              penalty: 3,
              points: 100,
            },
            username: 'omegaUp_virtual',
          },
          {
            classname: 'user-rank-unranked',
            country: 'MX',
            is_invited: true,
            place: 2,
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
              points: 0,
            },
            username: 'omegaUp',
            virtual: true,
          },
        ],
        start_time: expect.any(String),
        time: expect.any(String),
        title: 'contest',
      });
    });

    it('Should handle onVirtualRankingChanged function', () => {
      const localVue = createLocalVue();
      localVue.use(Vuex);
      const store = new Vuex.Store(rankingStoreConfig);

      onVirtualRankingChanged({
        scoreboard,
        scoreboardEvents: originalScoreboardEvents,
        problems: navbarProblems,
        startTime: new Date(0),
        finishTime: new Date(1),
        currentUsername: 'omegaUp',
        scoreMode: ScoreMode.Partial,
      });

      expect(store.state.ranking).toEqual([
        {
          country: 'MX',
          name: 'omegaUp [virtual]',
          username: 'omegaUp_virtual',
          classname: 'user-rank-unranked',
          is_invited: true,
          problems: [
            {
              alias: 'problem_alias',
              penalty: 3,
              percent: 100,
              points: 100,
              runs: 1,
            },
            {
              alias: 'problem_alias_2',
              penalty: 0,
              percent: 0,
              points: 0,
              runs: 0,
            },
          ],
          total: { points: 100, penalty: 3 },
          place: 1,
        },
        {
          classname: 'user-rank-unranked',
          username: 'omegaUp',
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
          total: { points: 0, penalty: 0 },
          virtual: true,
          place: 2,
        },
      ]);
      expect(store.state.miniRankingUsers).toEqual([
        {
          position: 1,
          username: 'omegaUp_virtual (omegaUp [virtual])',
          country: 'MX',
          classname: 'user-rank-unranked',
          points: 100,
          penalty: 3,
        },
        {
          position: 2,
          username: 'omegaUp [virtual]',
          country: 'MX',
          classname: 'user-rank-unranked',
          points: 0,
          penalty: 0,
        },
      ]);
      expect(store.state.rankingChartOptions.series).toBeTruthy();
    });
  });

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
        scoreMode: ScoreMode.Partial,
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
        scoreMode: ScoreMode.Partial,
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
        scoreMode: ScoreMode.Partial,
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
