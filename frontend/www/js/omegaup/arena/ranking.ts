import * as ui from '../ui';
import { types } from '../api_types';
import { myRunsStore } from './runsStore';
import { omegaup } from '../omegaup';
import { getMaxScore, getScoreForProblem, ScoreMode } from './navigation';
import T from '../lang';
import rankingStore from './rankingStore';

export const scoreboardColors = [
  '#FB3F51',
  '#FF5D40',
  '#FFA240',
  '#FFC740',
  '#59EA3A',
  '#37DD6F',
  '#34D0BA',
  '#3AAACF',
  '#8144D6',
  '#CD35D3',
];

export interface RankingRequest {
  problemsetId: number;
  scoreboardToken: string;
  currentUsername: string;
  navbarProblems: types.NavbarProblemsetProblem[];
}

export function onRankingEvents({
  events,
  currentRanking,
  startTimestamp = 0,
  finishTimestamp = Date.now(),
  placesToShowInChart = 10,
}: {
  events: types.ScoreboardEvent[];
  currentRanking: { [username: string]: number };
  startTimestamp?: number;
  finishTimestamp?: number;
  placesToShowInChart?: number;
}): {
  series: (Highcharts.SeriesLineOptions & { rank: number })[];
  navigatorData: number[][];
} {
  const dataInSeries: { [name: string]: number[][] } = {};
  const navigatorData: number[][] = [[startTimestamp, 0]];
  const series: (Highcharts.SeriesLineOptions & { rank: number })[] = [];
  const usernames: { [name: string]: string } = {};

  // Don't trust input data (data might not be sorted)
  events.sort((a, b) => a.delta - b.delta);

  // group points by person
  for (const curr of events) {
    // limit chart to top n users
    if (currentRanking[curr.username] > placesToShowInChart) continue;

    const name = curr.name ?? curr.username;

    if (!dataInSeries[name]) {
      dataInSeries[name] = [[startTimestamp, 0]];
      usernames[name] = curr.username;
    }
    dataInSeries[name].push([
      startTimestamp + curr.delta * 60 * 1000,
      curr.total.points,
    ]);

    // check if to add to navigator
    if (curr.total.points > navigatorData[navigatorData.length - 1][1]) {
      navigatorData.push([
        startTimestamp + curr.delta * 60 * 1000,
        curr.total.points,
      ]);
    }
  }

  // convert datas to series
  for (const name of Object.keys(dataInSeries)) {
    dataInSeries[name].push([
      finishTimestamp,
      dataInSeries[name][dataInSeries[name].length - 1][1],
    ]);
    series.push({
      type: 'line',
      name: name,
      rank: currentRanking[usernames[name]],
      data: dataInSeries[name],
      step: 'right',
    });
  }

  series.sort((a, b) => a.rank - b.rank);

  navigatorData.push([
    finishTimestamp,
    navigatorData[navigatorData.length - 1][1],
  ]);

  return { series, navigatorData };
}

export function createChart({
  series,
  navigatorData,
  maxPoints,
  startTimestamp,
  finishTimestamp,
}: {
  series: (Highcharts.SeriesLineOptions & { rank: number })[];
  navigatorData: number[][];
  maxPoints: number;
  startTimestamp: number;
  finishTimestamp: number;
}): Highcharts.Options {
  return {
    chart: { height: 300, spacingTop: 20 },

    colors: scoreboardColors,

    xAxis: {
      type: 'datetime',
      ordinal: false,
      min: startTimestamp,
      max: finishTimestamp,
    },

    yAxis: {
      showLastLabel: true,
      showFirstLabel: false,
      min: 0,
      max: maxPoints,
      title: {
        text: T.wordsSubmissions,
      },
    },

    plotOptions: {
      series: {
        animation: false,
        lineWidth: 3,
        states: { hover: { lineWidth: 3 } },
        marker: { radius: 5, symbol: 'circle', lineWidth: 1 },
      },
    },

    title: {
      text: T.wordsSubmissions,
    },

    navigator: {
      series: {
        type: 'line',
        step: 'left',
        lineWidth: 3,
        lineColor: '#333',
        data: navigatorData,
      },
    },

    rangeSelector: { enabled: false },

    series: series,
  };
}

export function updateProblemScore({
  alias,
  previousScore,
  scoreboard,
  username,
}: {
  alias: string;
  previousScore: number;
  scoreboard: types.Scoreboard;
  username: string;
}): types.ScoreboardRankingEntry[] {
  return scoreboard.ranking.map((rank) => {
    const ranking = rank;
    if (ranking.username === username) {
      ranking.problems = rank.problems.map((problem) => {
        const problemRanking = problem;
        if (problemRanking.alias == alias) {
          const maxScore = getMaxScore(
            myRunsStore.state.runs.filter((run) => run.alias === problem.alias),
            problemRanking.alias,
            previousScore,
          );
          problemRanking.points = maxScore;
        }
        return problemRanking;
      });
      ranking.total.points = rank.problems.reduce(
        (accumulator, problem) => accumulator + problem.points,
        0,
      );
    }
    return ranking;
  });
}

export function onRankingChanged({
  scoreboard,
  currentUsername,
  navbarProblems,
  scoreMode,
}: {
  scoreboard: types.Scoreboard;
  currentUsername: string;
  navbarProblems: types.NavbarProblemsetProblem[];
  scoreMode: ScoreMode;
}): {
  ranking: types.ScoreboardRankingEntry[];
  users: omegaup.UserRank[];
  currentRanking: { [username: string]: number };
  maxPoints: number;
  lastTimeUpdated: Date;
} {
  const users: omegaup.UserRank[] = [];
  const problems: { [alias: string]: types.NavbarProblemsetProblem } = {};
  const ranking: types.ScoreboardRankingEntry[] = scoreboard.ranking;
  const currentRanking: { [username: string]: number } = {};
  const order: { [problemAlias: string]: number } = {};
  const currentRankingState: { [username: string]: { place: number } } = {};
  let maxPoints: number = 0;

  for (const [i, problem] of scoreboard.problems.entries()) {
    order[problem.alias] = i;
  }

  for (const problem of navbarProblems) {
    problems[problem.alias] = problem;
    maxPoints += problem.maxScore;
  }

  // Push scoreboard to ranking table
  for (const [i, rank] of scoreboard.ranking.entries()) {
    currentRanking[rank.username] = i;
    const username = ui.rankingUsername(rank);
    currentRankingState[username] = { place: rank.place ?? 0 };

    // Update problem scores.
    for (const alias of Object.keys(order)) {
      const problem = rank.problems[order[alias]];
      if (
        problems[alias] &&
        rank.username === currentUsername &&
        problems[alias].acceptsSubmissions
      ) {
        const currentProblem = problems[alias];

        currentProblem.hasRuns = problem.runs > 0;
        currentProblem.bestScore = getScoreForProblem({
          contestMode: scoreMode,
          problemAlias: problem.alias,
          problemPoints: problem.points,
        });
      }
    }

    // update miniranking
    if (i < 10) {
      const username = ui.rankingUsername(rank);
      users.push({
        position: currentRankingState[username].place,
        username,
        country: rank.country,
        classname: rank.classname,
        points: rank.total.points,
        penalty: rank.total.penalty,
      });
    }
  }
  return {
    ranking,
    users,
    currentRanking,
    maxPoints,
    lastTimeUpdated: scoreboard.time,
  };
}

export function mergeRankings({
  scoreboard,
  originalScoreboardEvents,
  navbarProblems,
}: {
  scoreboard: types.Scoreboard;
  originalScoreboardEvents: types.ScoreboardEvent[];
  navbarProblems: types.NavbarProblemsetProblem[];
}): {
  mergedScoreboard: types.Scoreboard;
  originalContestEvents: types.ScoreboardEvent[];
} {
  // This clones virtualContestData to data so that virtualContestData values
  // won't be overriden by processes below
  const data = JSON.parse(JSON.stringify(scoreboard)) as types.Scoreboard;
  const dataRanking: (types.ScoreboardRankingEntry & {
    virtual?: boolean;
  })[] = data.ranking;
  const events = originalScoreboardEvents;
  const currentDelta =
    (new Date().getTime() - scoreboard.start_time.getTime()) / (1000 * 60);

  for (const rank of dataRanking) rank.virtual = true;

  const problemOrder: { [problemAlias: string]: number } = {};
  const problems: { order: number; alias: string }[] = [];
  const initialProblems: types.ScoreboardRankingProblem[] = [];

  for (const problem of Object.values(navbarProblems)) {
    problemOrder[problem.alias] = problems.length;
    initialProblems.push({
      alias: problem.alias,
      penalty: 0,
      percent: 0,
      points: 0,
      runs: 0,
    });
    problems.push({ order: problems.length + 1, alias: problem.alias });
  }

  // Calculate original contest scoreboard with current delta time
  const originalContestRanking: {
    [username: string]: types.ScoreboardRankingEntry;
  } = {};
  const originalContestEvents: types.ScoreboardEvent[] = [];

  // Refresh after time T
  let refreshTime = 30 * 1000; // 30 seconds

  events.forEach((evt: types.ScoreboardEvent) => {
    const key = evt.username;
    if (!Object.prototype.hasOwnProperty.call(originalContestRanking, key)) {
      originalContestRanking[key] = {
        country: evt.country,
        name: evt.name,
        username: evt.username,
        classname: evt.classname,
        is_invited: evt.is_invited,
        problems: Array.from(initialProblems),
        total: {
          penalty: 0,
          points: 0,
        },
        place: 0,
      };
    }
    if (evt.delta > currentDelta) {
      refreshTime = Math.min(
        refreshTime,
        (evt.delta - currentDelta) * 60 * 1000,
      );
      return;
    }
    originalContestEvents.push(evt);
    const problem =
      originalContestRanking[key].problems[problemOrder[evt.problem.alias]];
    originalContestRanking[key].problems[problemOrder[evt.problem.alias]] = {
      alias: evt.problem.alias,
      penalty: evt.problem.penalty,
      points: evt.problem.points,
      percent: evt.problem.points,
      // If problem appeared in event for than one, it means a problem has
      // been solved multiple times
      runs: problem ? problem.runs + 1 : 1,
    };
    originalContestRanking[key].total = evt.total;
  });
  // Merge original contest scoreboard ranking with virtual contest
  for (const ranking of Object.values(originalContestRanking)) {
    dataRanking.push(ranking);
  }

  // Re-sort rank
  dataRanking.sort((rank1, rank2) => {
    return rank2.total.points - rank1.total.points;
  });

  // Override ranking
  dataRanking.forEach((rank, index) => (rank.place = index + 1));
  const mergedScoreboard = data;
  mergedScoreboard.ranking = dataRanking;
  return { mergedScoreboard, originalContestEvents };
}

export function onVirtualRankingChanged({
  scoreboard,
  scoreboardEvents,
  problems,
  startTime,
  finishTime,
  currentUsername,
  scoreMode,
}: {
  scoreboard: types.Scoreboard;
  scoreboardEvents: types.ScoreboardEvent[];
  problems: types.NavbarProblemsetProblem[];
  startTime: Date;
  finishTime?: Date;
  currentUsername: string;
  scoreMode: ScoreMode;
}): void {
  let rankingChartOptions: Highcharts.Options | null = null;
  const { mergedScoreboard, originalContestEvents } = mergeRankings({
    scoreboard,
    originalScoreboardEvents: scoreboardEvents,
    navbarProblems: problems,
  });
  const rankingInfo = onRankingChanged({
    scoreboard: mergedScoreboard,
    currentUsername: currentUsername,
    navbarProblems: problems,
    scoreMode,
  });
  const ranking = rankingInfo.ranking;
  const users = rankingInfo.users;
  const lastTimeUpdated = rankingInfo.lastTimeUpdated;
  rankingStore.commit('updateRanking', ranking);
  rankingStore.commit('updateMiniRankingUsers', users);
  rankingStore.commit('updateLastTimeUpdated', lastTimeUpdated);

  const startTimestamp = startTime.getTime();
  const finishTimestamp = Math.min(
    finishTime?.getTime() || Infinity,
    Date.now(),
  );
  const { series, navigatorData } = onRankingEvents({
    events: scoreboardEvents.concat(originalContestEvents),
    currentRanking: rankingInfo.currentRanking,
    startTimestamp,
    finishTimestamp,
  });
  if (series.length) {
    rankingChartOptions = createChart({
      series,
      navigatorData,
      startTimestamp: startTimestamp ?? Date.now(),
      finishTimestamp,
      maxPoints: rankingInfo.maxPoints,
    });
    rankingStore.commit('updateRankingChartOptions', rankingChartOptions);
  }
}
