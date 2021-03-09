import Vue from 'vue';
import arena_ContestPractice from '../components/arena/ContestPractice.vue';
import problem_Details from '../components/problem/Details.vue';
import * as api from '../api';
import * as ui from '../ui';
import * as time from '../time';
import { types } from '../api_types';
import { myRunsStore, runsStore } from './runsStore';
import { OmegaUp } from '../omegaup';

export function submitRun(request: any): void {
  api.Run.create({
    problem_alias: request.problemAlias,
    language: request.language,
    source: request.code,
  })
    .then((response) => {
      ui.reportEvent('submission', 'submit');

      updateRun(
        {
          guid: response.guid,
          submit_delay: response.submit_delay,
          username: request.username,
          classname: request.classname,
          country: 'xx',
          status: 'new',
          alias: request.problemAlias,
          time: new Date(),
          penalty: 0,
          runtime: 0,
          memory: 0,
          verdict: 'JE',
          score: 0,
          language: request.selectedLanguage,
        },
        request.target,
      );
    })
    .catch((run) => {
      ui.error(run.error ?? run);
      if (run.errorname) {
        ui.reportEvent('submission', 'submit-fail', run.errorname);
      }
    });
}

export function updateRun(
  run: types.Run,
  target: arena_ContestPractice | problem_Details,
): void {
  trackRun(run, target);

  // TODO: Implement websocket support

  if (run.status != 'ready') {
    updateRunFallback(run.guid, target);
    return;
  }
}

export function updateRunFallback(guid: string, target: any): void {
  setTimeout(() => {
    api.Run.status({ run_alias: guid })
      .then(time.remoteTimeAdapter)
      .then((response) => updateRun(response, target))
      .catch(ui.ignoreError);
  }, 5000);
}

export function trackRun(run: types.Run, target: any): void {
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

export function refreshRuns(problemAlias: string, target: any): void {
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
        trackRun(run, target);
      }
    })
    .catch(ui.apiError);
}
