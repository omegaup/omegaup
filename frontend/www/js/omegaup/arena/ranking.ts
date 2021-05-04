import * as ui from '../ui';
import { types } from '../api_types';
import { myRunsStore } from './runsStore';
import { omegaup } from '../omegaup';
import { getMaxScore } from './navigation';

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
  startTimestamp?: number;
  finishTimestamp?: number;
  placesToShowInChart?: number;
  currentRanking: { [username: string]: number };
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
}: {
  series: (Highcharts.SeriesLineOptions & { rank: number })[];
  navigatorData: number[][];
}): {
  series: (Highcharts.SeriesLineOptions & { rank: number })[];
  navigatorData: number[][];
} {
  // TODO: Implement this function in a new PR
  return { series, navigatorData };
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
            myRunsStore.state.runs,
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
}: {
  scoreboard: types.Scoreboard;
  currentUsername: string;
  navbarProblems: types.NavbarProblemsetProblem[];
}): {
  ranking: types.ScoreboardRankingEntry[];
  users: omegaup.UserRank[];
  currentRanking: { [username: string]: number };
} {
  const users: omegaup.UserRank[] = [];
  const problems: { [alias: string]: types.NavbarProblemsetProblem } = {};
  const ranking: types.ScoreboardRankingEntry[] = scoreboard.ranking;
  const currentRanking: { [username: string]: number } = {};
  const order: { [problemAlias: string]: number } = {};
  const currentRankingState: { [username: string]: { place: number } } = {};

  for (const [i, problem] of scoreboard.problems.entries()) {
    order[problem.alias] = i;
  }

  for (const problem of navbarProblems) {
    problems[problem.alias] = problem;
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
        currentProblem.bestScore = getMaxScore(
          myRunsStore.state.runs,
          alias,
          problem.points,
        );
        currentProblem.maxScore = problem.points;
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
  return { ranking, users, currentRanking };
}
