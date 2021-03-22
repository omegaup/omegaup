import * as ui from '../ui';
import { types } from '../api_types';
import { getMaxScore } from './navigation';
import { myRunsStore } from './runsStore';
import { omegaup } from '../omegaup';

interface Problem {
  accepts_submissions: boolean;
  alias: string;
  commit: string;
  input_limit: number;
  languages: string[];
  lastSubmission?: Date;
  letter?: string;
  nextSubmissionTimestamp?: Date;
  points: number;
  problemsetter?: types.ProblemsetterInfo;
  quality_seal: boolean;
  quality_payload?: types.ProblemQualityPayload;
  runs?: types.Run[];
  settings?: types.ProblemSettingsDistrib;
  source?: string;
  statement?: types.ProblemStatement;
  title: string;
  visibility: number;
}

export interface RankingRequest {
  problemsetId: number;
  scoreboardToken: string;
  currentUsername: string;
  navbarProblems: types.NavbarProblemsetProblem[];
}

// Implement this function in a new PR
export function onRankingEvents(events: types.ScoreboardEvent[]): void {
  // Don't trust input data (data might not be sorted)
  // TODO: use events
  events.sort((a, b) => a.delta - b.delta);
  createChart();
}

export function createChart(): void {
  // Implement this function in a new PR
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
} {
  const users: omegaup.UserRank[] = [];
  const problems: { [alias: string]: Problem } = {};
  const ranking: types.ScoreboardRankingEntry[] = scoreboard.ranking;
  const newRanking: { [username: string]: number } = {};
  const order: { [problemAlias: string]: number } = {};
  const currentRankingState: { [username: string]: { place: number } } = {};

  for (const [i, problem] of scoreboard.problems.entries()) {
    order[problem.alias] = i;
  }

  // Push scoreboard to ranking table
  for (const [i, rank] of ranking.entries()) {
    newRanking[rank.username] = i;
    const username = ui.rankingUsername(rank);
    currentRankingState[username] = { place: rank.place ?? 0 };

    // Update problem scores.
    for (const alias of Object.keys(order)) {
      const problem = rank.problems[order[alias]];
      if (
        problems[alias] &&
        rank.username === currentUsername &&
        problems[alias].languages.length > 0
      ) {
        const currentPoints = problems[alias].points;

        const currentProblem = navbarProblems.find(
          (problem) => problem.alias === alias,
        );

        if (currentProblem) {
          currentProblem.hasRuns = problem.runs > 0;
          currentProblem.bestScore = getMaxScore(
            myRunsStore.state.runs,
            alias,
            problem.points,
          );
          currentProblem.maxScore = currentPoints;
        }
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
  return { ranking, users };
}
