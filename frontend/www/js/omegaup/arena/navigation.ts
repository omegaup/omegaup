import Vue from 'vue';
import * as api from '../api';
import * as ui from '../ui';
import * as time from '../time';
import { setLocationHash } from '../location';
import { types } from '../api_types';
import { myRunsStore } from './runsStore';
import problemsStore from './problemStore';
import { PopupDisplayed } from '../components/problem/Details.vue';
import { trackRun } from './submissions';

export enum ScoreMode {
  AllOrNothing = 'all_or_nothing',
  Partial = 'partial',
  MaxPerGroup = 'max_per_group',
}

export enum NavigationType {
  ForContest,
  ForSingleProblemOrCourse,
}

interface BaseNavigation {
  target: Vue & {
    problemInfo: types.ProblemInfo | null;
    popupDisplayed?: PopupDisplayed;
    problem: types.NavbarProblemsetProblem | null;
  };
  problem: types.NavbarProblemsetProblem;
  problems: types.NavbarProblemsetProblem[];
}

type NavigationForContest = BaseNavigation & {
  type: NavigationType.ForContest;
  contestAlias: string;
  contestMode: ScoreMode;
};

type NavigationForSingleProblemOrCourse = BaseNavigation & {
  type: NavigationType.ForSingleProblemOrCourse;
  problemsetId: number;
  guid?: string;
};

export type NavigationRequest =
  | NavigationForContest
  | NavigationForSingleProblemOrCourse;

export function getScoreModeEnum(scoreMode: string): ScoreMode {
  if (scoreMode === 'max_per_group') return ScoreMode.MaxPerGroup;
  if (scoreMode === 'all_or_nothing') return ScoreMode.AllOrNothing;
  return ScoreMode.Partial;
}

export async function navigateToProblem(
  request: NavigationRequest,
): Promise<void> {
  let contestAlias, problemsetId, guid, contestMode: ScoreMode;
  if (request.type === NavigationType.ForContest) {
    contestAlias = request.contestAlias;
    contestMode = request.contestMode;
  } else if (request.type === NavigationType.ForSingleProblemOrCourse) {
    problemsetId = request.problemsetId;
    guid = request.guid;
  }
  const { target, problem, problems } = request;
  if (
    Object.prototype.hasOwnProperty.call(
      problemsStore.state.problems,
      problem.alias,
    )
  ) {
    target.problemInfo = problemsStore.state.problems[problem.alias];
    if (target.popupDisplayed === PopupDisplayed.RunSubmit) {
      setLocationHash(`#problems/${problem.alias}/new-run`);
      return;
    }
    if (guid) {
      setLocationHash(`#problems/${problem.alias}/show-run:${guid}`);
      return;
    }
    setLocationHash(`#problems/${problem.alias}`);
    return;
  }
  return api.Problem.details({
    problem_alias: problem.alias,
    prevent_problemset_open: false,
    contest_alias: contestAlias,
    problemset_id: problemsetId,
  })
    .then(time.remoteTimeAdapter)
    .then((problemDetails) => {
      for (const run of problemDetails.runs ?? []) {
        trackRun({ run });
      }
      const currentProblem = problems?.find(
        ({ alias }: { alias: string }) => alias === problemDetails.alias,
      );
      problemDetails.title = currentProblem?.text ?? '';
      target.problemInfo = problemDetails;
      problem.alias = problemDetails.alias;
      problem.bestScore = getScoreForProblem({
        contestMode,
        problemAlias: problemDetails.alias,
        previousScore: 0.0,
        maxScore: problem.maxScore,
      });
      const problemInfo: types.ProblemInfo = {
        accepts_submissions: problemDetails.accepts_submissions,
        alias: problemDetails.alias,
        commit: problemDetails.commit,
        input_limit: problemDetails.input_limit,
        karel_problem: problemDetails.karel_problem,
        lastOpenedTimestamp: Date.now(),
        languages: problemDetails.languages,
        limits: problemDetails.limits,
        points: problemDetails.points,
        problem_id: problemDetails.problem_id,
        quality_seal: problemDetails.quality_seal,
        secondsToNextSubmission: problemDetails.secondsToNextSubmission,
        settings: problemDetails.settings,
        statement: problemDetails.statement,
        title: problemDetails.title,
        visibility: problemDetails.visibility,
      };
      problemsStore.commit('addProblem', problemInfo);
      target.problem = problem;
      if (target.popupDisplayed === PopupDisplayed.RunSubmit) {
        setLocationHash(`#problems/${problem.alias}/new-run`);
        return;
      }
      setLocationHash(`#problems/${problem.alias}`);
    })
    .catch(() => {
      ui.dismissNotifications();
      target.problem = null;
      setLocationHash('#problems');
    });
}

export function getScoreForProblem({
  contestMode,
  problemAlias,
  previousScore,
  maxScore,
}: {
  contestMode: ScoreMode;
  problemAlias: string;
  previousScore: number;
  maxScore: number;
}) {
  if (contestMode === ScoreMode.MaxPerGroup) {
    return getMaxPerGroupScore(
      myRunsStore.state.runs.filter((run) => run.alias === problemAlias),
      problemAlias,
      previousScore,
      maxScore,
    );
  }
  return getMaxScore(
    myRunsStore.state.runs.filter((run) => run.alias === problemAlias),
    problemAlias,
    previousScore,
  );
}

export function getMaxScore(
  runs: types.Run[],
  alias: string,
  previousScore: number,
): number {
  let maxScore = previousScore;
  for (const run of runs) {
    if (alias != run.alias) {
      continue;
    }
    maxScore = Math.max(maxScore, run.contest_score || 0);
  }
  return maxScore;
}

export function getMaxPerGroupScore(
  runs: types.Run[],
  alias: string,
  previousScore: number,
  maxScore: number,
): number {
  const runsWithScoreByGroup = runs
    .filter((run) => run.alias === alias)
    .filter((run) => run.score_by_group != undefined);
  if (!runsWithScoreByGroup.length) {
    return previousScore;
  }

  const scoreByGroup = Object.keys(
    runsWithScoreByGroup[0].score_by_group || {},
  ).reduce((acc: Record<string, number>, key) => {
    const values = runsWithScoreByGroup
      .map((run) => {
        if (!run.score_by_group) {
          return 0.0;
        }
        return (run.score_by_group[key] as number) * maxScore;
      })
      .filter((value) => value !== null)
      .map((value) => Number(value));
    acc[key] = Math.max(...values);
    return acc;
  }, {});

  const values = Object.values(scoreByGroup);

  // Avoid showing NaN in bestScore value
  for (const value of values) {
    if (typeof value === 'undefined') {
      return 0;
    }
  }

  return values.reduce((acc, value) => acc + value, 0);
}
