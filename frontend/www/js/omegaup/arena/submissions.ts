import Vue from 'vue';
import * as api from '../api';
import * as ui from '../ui';
import * as time from '../time';
import { types } from '../api_types';
import { myRunsStore, runsStore } from './runsStore';
import { OmegaUp } from '../omegaup';

interface RunSubmit {
  classname: string;
  username: string;
  guid: string;
  submitDelay: number;
  language: string;
  problemAlias: string;
  target: Vue & { nominationStatus?: types.NominationStatus };
  runs?: types.Run[];
  problem?: types.NavbarProblemsetProblem;
}

export function submitRun({
  classname,
  username,
  guid,
  language,
  problemAlias,
  submitDelay,
  target,
}: RunSubmit): void {
  ui.reportEvent('submission', 'submit');
  const run: types.Run = {
    guid: guid,
    submit_delay: submitDelay,
    username,
    classname,
    country: 'xx',
    status: 'new',
    alias: problemAlias,
    time: new Date(),
    penalty: 0,
    runtime: 0,
    memory: 0,
    verdict: 'JE',
    score: 0,
    language,
  };
  updateRun({ run, target });
}

export function submitRunFailed({
  error,
  errorname,
  run,
}: {
  error: string;
  errorname: string;
  run: types.Run;
}): void {
  ui.error(error ?? run);
  if (errorname) {
    ui.reportEvent('submission', 'submit-fail', errorname);
  }
}

export function updateRun({
  run,
  target,
}: {
  run: types.Run;
  target: Vue & { nominationStatus?: types.NominationStatus };
}): void {
  trackRun({ run, target });

  // TODO: Implement websocket support

  if (run.status != 'ready') {
    updateRunFallback({ run, target });
    return;
  }
}

export function updateRunFallback({
  run,
  target,
}: {
  run: types.Run;
  target: Vue & { nominationStatus?: types.NominationStatus };
}): void {
  setTimeout(() => {
    api.Run.status({ run_alias: run.guid })
      .then(time.remoteTimeAdapter)
      .then((response) => updateRun({ run: response, target }))
      .catch(ui.ignoreError);
  }, 5000);
}

export function trackRun({
  run,
  target,
}: {
  run: types.Run;
  target: Vue & { nominationStatus?: types.NominationStatus };
}): void {
  runsStore.commit('addRun', run);
  if (run.username !== OmegaUp.username) {
    return;
  }
  myRunsStore.commit('addRun', run);

  if (!target.nominationStatus) {
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

export function refreshRuns({
  problemAlias,
  target,
}: {
  problemAlias: string;
  target: Vue & { nominationStatus?: types.NominationStatus };
}): void {
  api.Problem.runs({
    problem_alias: problemAlias,
    show_all: true,
    offset: runsStore.state.filters?.offset,
    rowcount: runsStore.state.filters?.rowcount,
    verdict: runsStore.state.filters?.verdict,
    language: runsStore.state.filters?.language,
    username: runsStore.state.filters?.username,
    status: runsStore.state.filters?.status,
  })
    .then(time.remoteTimeAdapter)
    .then((response) => {
      runsStore.commit('clear');
      for (const run of response.runs) {
        trackRun({ run, target });
      }
    })
    .catch(ui.apiError);
}
