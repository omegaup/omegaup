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
  let contestAlias, problemsetId, contestMode: ScoreMode;
  if (request.type === NavigationType.ForContest) {
    contestAlias = request.contestAlias;
    contestMode = request.contestMode;
  } else if (request.type === NavigationType.ForSingleProblemOrCourse) {
    problemsetId = request.problemsetId;
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
    .then((problemInfo) => {
      for (const run of problemInfo.runs ?? []) {
        trackRun({ run });
      }
      const currentProblem = problems?.find(
        ({ alias }: { alias: string }) => alias === problemInfo.alias,
      );
      problemInfo.title = currentProblem?.text ?? '';
      target.problemInfo = problemInfo;
      problem.alias = problemInfo.alias;
      problem.bestScore = getScoreForProblem({
        contestMode,
        problemAlias: problemInfo.alias,
        problemPoints: 0.0,
      });
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
  problemPoints,
}: {
  contestMode: ScoreMode;
  problemAlias: string;
  problemPoints: number;
}) {
  if (contestMode === ScoreMode.MaxPerGroup) {
    return getMaxPerGroupScore(
      myRunsStore.state.runs.filter((run) => run.alias === problemAlias),
      problemAlias,
      problemPoints,
    );
  }
  return getMaxScore(
    myRunsStore.state.runs.filter((run) => run.alias === problemAlias),
    problemAlias,
    problemPoints,
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

function getMaxPerGroupScore(
  runs: types.Run[],
  alias: string,
  previousScore: number,
): number {
  if (!runs.length) {
    return previousScore;
  }
  const scoreByGroup = Object.keys(runs[0].score_by_group || {}).reduce(
    (acc: Record<string, number>, key) => {
      const values = runs
        .filter((run) => run.alias === alias)
        .map((run) => (run.score_by_group ? run.score_by_group[key] : 0.0))
        .filter((value) => value !== null)
        .map((value) => Number(value));
      acc[key] = Math.max(...values);
      return acc;
    },
    {},
  );

  const values = Object.values(scoreByGroup);
  return values.reduce((acc, value) => acc + value, 0);
}
