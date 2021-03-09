import Vue from 'vue';
import * as api from '../api';
import * as ui from '../ui';
import { types } from '../api_types';
import { myRunsStore, runsStore } from './runsStore';
import { OmegaUp } from '../omegaup';
import problemsStore from './problemStore';
import { PopupDisplayed } from '../components/problem/Details.vue';
import { ActiveProblem } from '../components/arena/ContestPractice.vue';

interface Navigation {
  target: Vue & {
    problemInfo: types.ProblemInfo | null;
    popupDisplayed?: PopupDisplayed;
    problem: ActiveProblem | null;
  };
  runs: types.Run[];
  problem: types.NavbarProblemsetProblem;
  problems: types.NavbarProblemsetProblem[];
}

export function navigateToProblem(request: Navigation): void {
  if (
    Object.prototype.hasOwnProperty.call(
      problemsStore.state.problems,
      request.problem.alias,
    )
  ) {
    request.target.problemInfo =
      problemsStore.state.problems[request.problem.alias];
    window.location.hash = `#problems/${request.problem.alias}`;
    return;
  }
  api.Problem.details({
    problem_alias: request.problem.alias,
    prevent_problemset_open: false,
  })
    .then((problemInfo) => {
      for (const run of problemInfo.runs ?? []) {
        trackRun({ run });
      }
      const currentProblem = request.problems?.find(
        ({ alias }: { alias: string }) => alias === problemInfo.alias,
      );
      problemInfo.title = currentProblem?.text ?? '';
      request.target.problemInfo = problemInfo;
      request.problem.alias = problemInfo.alias;
      request.runs = myRunsStore.state.runs;
      request.problem.bestScore = getMaxScore(
        request.runs,
        problemInfo.alias,
        0,
      );
      problemsStore.commit('addProblem', problemInfo);
      if (request.target.popupDisplayed === PopupDisplayed.RunSubmit) {
        window.location.hash = `#problems/${request.problem.alias}/new-run`;
        return;
      }
      window.location.hash = `#problems/${request.problem.alias}`;
    })
    .catch(() => {
      ui.dismissNotifications();
      request.target.problem = null;
      window.location.hash = '#problems';
    });
}

export function trackRun(request: {
  run: types.Run;
  target?: Vue & { nominationStatus?: types.NominationStatus };
}): void {
  runsStore.commit('addRun', request.run);
  if (request.run.username !== OmegaUp.username) {
    return;
  }
  myRunsStore.commit('addRun', request.run);

  if (!request.target?.nominationStatus) {
    return;
  }
  if (
    request.run.verdict !== 'AC' &&
    request.run.verdict !== 'CE' &&
    request.run.verdict !== 'JE'
  ) {
    request.target.nominationStatus.tried = true;
  }
  if (request.run.verdict === 'AC') {
    Vue.set(
      request.target,
      'nominationStatus',
      Object.assign({}, request.target.nominationStatus, {
        solved: true,
      }),
    );
  }
}

function getMaxScore(
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
