import Vue from 'vue';
import * as api from '../api';
import * as ui from '../ui';
import { setLocationHash } from '../location';
import { types } from '../api_types';
import { myRunsStore } from './runsStore';
import problemsStore from './problemStore';
import { PopupDisplayed } from '../components/problem/Details.vue';
import { ActiveProblem } from '../components/arena/ContestPractice.vue';
import { trackRun } from './submissions';

export enum NavigationType {
  ForContest,
  ForSingleProblemOrCourse,
}

interface BaseNavigation {
  target: Vue & {
    problemInfo: types.ProblemInfo | null;
    popupDisplayed?: PopupDisplayed;
    problem: ActiveProblem | null;
  };
  runs: types.Run[];
  problem: types.NavbarProblemsetProblem;
  problems: types.NavbarProblemsetProblem[];
}

type NavigationForContest = BaseNavigation & {
  type: NavigationType.ForContest;
  contestAlias: string;
};

type NavigationForSingleProblemOrCourse = BaseNavigation & {
  type: NavigationType.ForSingleProblemOrCourse;
};

export type NavigationRequest =
  | NavigationForContest
  | NavigationForSingleProblemOrCourse;

export async function navigateToProblem(
  request: NavigationRequest,
): Promise<void> {
  let contestAlias;
  if (request.type === NavigationType.ForContest) {
    contestAlias = request.contestAlias;
  }
  const { target, problem, problems } = request;
  let { runs } = request;
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
  })
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
      runs = myRunsStore.state.runs;
      problem.bestScore = getMaxScore(runs, problemInfo.alias, 0);
      problemsStore.commit('addProblem', problemInfo);
      target.problem = { problem, runs };
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
