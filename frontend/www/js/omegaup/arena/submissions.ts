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
  runs: types.Run[];
}

export function submitRun({
  classname,
  username,
  guid,
  language,
  problemAlias,
  submitDelay,
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
  updateRun({ run });
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

export function updateRun({ run }: { run: types.Run }): void {
  trackRun({ run });

  // TODO: Implement websocket support

  if (run.status != 'ready') {
    updateRunFallback({ run });
    return;
  }
}

export function updateRunFallback({ run }: { run: types.Run }): void {
  setTimeout(() => {
    api.Run.status({ run_alias: run.guid })
      .then(time.remoteTimeAdapter)
      .then((response) => updateRun({ run: response }))
      .catch(ui.ignoreError);
  }, 5000);
}

export function trackRun({ run }: { run: types.Run }): void {
  runsStore.commit('addRun', run);
  if (run.username !== OmegaUp.username) {
    return;
  }
  myRunsStore.commit('addRun', run);
}

export function onSetNominationStatus({
  run,
  nominationStatus,
}: {
  run: types.Run;
  nominationStatus: types.NominationStatus;
}): void {
  if (run.verdict !== 'AC' && run.verdict !== 'CE' && run.verdict !== 'JE') {
    Vue.set(nominationStatus, 'tried', true);
  }
  if (run.verdict === 'AC') {
    Vue.set(nominationStatus, 'solved', true);
  }
}

export function onRefreshRuns({ runs }: { runs: types.Run[] }): void {
  runsStore.commit('clear');
  for (const run of runs) {
    trackRun({ run });
  }
}
