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

export function navigateToProblem({
  target,
  runs,
  problem,
  problems,
}: Navigation): void {
  if (
    Object.prototype.hasOwnProperty.call(
      problemsStore.state.problems,
      problem.alias,
    )
  ) {
    target.problemInfo = problemsStore.state.problems[problem.alias];
    window.location.hash = `#problems/${problem.alias}`;
    return;
  }
  api.Problem.details({
    problem_alias: problem.alias,
    prevent_problemset_open: false,
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
        window.location.hash = `#problems/${problem.alias}/new-run`;
        return;
      }
      window.location.hash = `#problems/${problem.alias}`;
    })
    .catch(() => {
      ui.dismissNotifications();
      target.problem = null;
      window.location.hash = '#problems';
    });
}

export function trackRun({
  run,
  target,
}: {
  run: types.Run;
  target?: Vue & { nominationStatus?: types.NominationStatus };
}): void {
  runsStore.commit('addRun', run);
  if (run.username !== OmegaUp.username) {
    return;
  }
  myRunsStore.commit('addRun', run);

  if (!target?.nominationStatus) {
    return;
  }
  if (run.verdict !== 'AC' && run.verdict !== 'CE' && run.verdict !== 'JE') {
    target.nominationStatus.tried = true;
  }
  if (run.verdict === 'AC') {
    Vue.set(
      target,
      'nominationStatus',
      Object.assign({}, target.nominationStatus, {
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
