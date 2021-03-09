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
  code: string;
  language: string;
  problemAlias: string;
  target: Vue & { nominationStatus?: types.NominationStatus };
  runs?: types.Run[];
  problem?: types.NavbarProblemsetProblem;
}

export function submitRun(request: RunSubmit): void {
  api.Run.create({
    problem_alias: request.problemAlias,
    language: request.language,
    source: request.code,
  })
    .then((response) => {
      ui.reportEvent('submission', 'submit');
      const run = {
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
        language: request.language,
      };
      updateRun({ run, target: request.target });
    })
    .catch((run) => {
      ui.error(run.error ?? run);
      if (run.errorname) {
        ui.reportEvent('submission', 'submit-fail', run.errorname);
      }
    });
}

export function updateRun(request: {
  run: types.Run;
  target: Vue & { nominationStatus?: types.NominationStatus };
}): void {
  trackRun(request);

  // TODO: Implement websocket support

  if (request.run.status != 'ready') {
    updateRunFallback(request);
    return;
  }
}

export function updateRunFallback(request: {
  run: types.Run;
  target: Vue & { nominationStatus?: types.NominationStatus };
}): void {
  setTimeout(() => {
    api.Run.status({ run_alias: request.run.guid })
      .then(time.remoteTimeAdapter)
      .then((response) => updateRun({ run: response, target: request.target }))
      .catch(ui.ignoreError);
  }, 5000);
}

export function trackRun(request: {
  run: types.Run;
  target: Vue & { nominationStatus?: types.NominationStatus };
}): void {
  runsStore.commit('addRun', request.run);
  if (request.run.username !== OmegaUp.username) {
    return;
  }
  myRunsStore.commit('addRun', request.run);

  if (!request.target.nominationStatus) {
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

export function refreshRuns(request: {
  problemAlias: string;
  target: Vue & { nominationStatus?: types.NominationStatus };
}): void {
  api.Problem.runs({
    problem_alias: request.problemAlias,
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
        trackRun({ run, target: request.target });
      }
    })
    .catch(ui.apiError);
}
