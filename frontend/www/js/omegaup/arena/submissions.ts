import Vue from 'vue';
import * as api from '../api';
import * as ui from '../ui';
import * as time from '../time';
import { types } from '../api_types';
import { myRunsStore, runsStore } from './runsStore';
import { OmegaUp } from '../omegaup';
import JSZip from 'jszip';
import problem_Details from '../components/problem/Details.vue';
import { omegaup } from '../omegaup';
import T from '../lang';

interface RunSubmit {
  classname: string;
  username: string;
  guid: string;
  submitDelay: number;
  language: string;
  problemAlias: string;
  runs: types.Run[];
}

interface SubmissionRequestWithExtraData {
  redirect: string;
  request: { guid: string; isAdmin: boolean; problemAlias: string };
  target: problem_Details;
  data: types.RunDetails;
}

export interface SubmissionRequest {
  request: { guid: string; isAdmin: boolean; problemAlias: string };
  target: problem_Details;
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

export function showSubmission({
  request,
  target,
  data,
  redirect,
}: SubmissionRequestWithExtraData) {
  if (data.show_diff === 'none' || !request.isAdmin) {
    displayRunDetails({
      request,
      data,
      target,
      redirect,
    });
    return;
  }
  fetch(`/api/run/download/run_alias/${request.guid}/show_diff/true/`)
    .then((response) => {
      if (!response.ok) {
        return Promise.reject(new Error(response.statusText));
      }
      return Promise.resolve(response.blob());
    })
    .then(JSZip.loadAsync)
    .then((zip: JSZip) => {
      const result: {
        cases: string[];
        promises: Promise<string>[];
      } = { cases: [], promises: [] };
      zip.forEach(async (relativePath, zipEntry) => {
        const pos = relativePath.lastIndexOf('.');
        const basename = relativePath.substring(0, pos);
        const extension = relativePath.substring(pos + 1);
        if (extension !== 'out' || relativePath.indexOf('/') !== -1) {
          return;
        }
        if (
          data.show_diff === 'examples' &&
          relativePath.indexOf('sample/') === 0
        ) {
          return;
        }
        result.cases.push(basename);
        result.promises.push(zip.file(zipEntry.name).async('text'));
      });
      return result;
    })
    .then((response) => {
      Promise.allSettled(response.promises).then((results) => {
        results.forEach((result: any, index: number) => {
          if (data.cases[response.cases[index]]) {
            data.cases[response.cases[index]].contestantOutput = result.value;
          }
        });
      });
      displayRunDetails({
        request,
        data,
        target,
        redirect,
      });
    })
    .catch(ui.apiError);
}

function displayRunDetails({
  request,
  target,
  data,
  redirect,
}: SubmissionRequestWithExtraData): void {
  let sourceHTML,
    sourceLink = false;
  if (data.source?.indexOf('data:') === 0) {
    sourceLink = true;
    sourceHTML = data.source;
  } else if (data.source == 'lockdownDetailsDisabled') {
    sourceHTML =
      (typeof sessionStorage !== 'undefined' &&
        sessionStorage.getItem(`run:${request.guid}`)) ||
      T.lockdownDetailsDisabled;
  } else {
    sourceHTML = data.source;
  }

  const detailsGroups = data.details && data.details.groups;
  const numericSort = <T extends { [key: string]: any }>(key: string) => {
    const isDigit = (ch: string) => '0' <= ch && ch <= '9';
    return (x: T, y: T) => {
      let i = 0,
        j = 0;
      for (; i < x[key].length && j < y[key].length; i++, j++) {
        if (isDigit(x[key][i]) && isDigit(x[key][j])) {
          let nx = 0,
            ny = 0;
          while (i < x[key].length && isDigit(x[key][i]))
            nx = nx * 10 + parseInt(x[key][i++]);
          while (j < y[key].length && isDigit(y[key][j]))
            ny = ny * 10 + parseInt(y[key][j++]);
          i--;
          j--;
          if (nx != ny) return nx - ny;
        } else if (x[key][i] < y[key][j]) {
          return -1;
        } else if (x[key][i] > y[key][j]) {
          return 1;
        }
      }
      return x[key].length - i - (y[key].length - j);
    };
  };

  let groups = undefined;
  if (detailsGroups && detailsGroups.length) {
    detailsGroups.sort(numericSort('group'));
    for (const detailGroup of detailsGroups) {
      if (!detailGroup.cases) {
        continue;
      }
      detailGroup.cases.sort(numericSort('name'));
    }
    groups = detailsGroups;
  }

  Vue.set(
    target,
    'currentRunDetailsData',
    Object.assign({}, data, {
      logs: data.logs || '',
      judged_by: data.judged_by || '',
      source: sourceHTML,
      source_link: sourceLink,
      source_url: window.URL.createObjectURL(
        new Blob([data.source || ''], { type: 'text/plain' }),
      ),
      source_name: `Main.${data.language}`,
      groups: groups,
      show_diff: request.isAdmin ? data.show_diff : 'none',
      feedback: omegaup.SubmissionFeedback.None as omegaup.SubmissionFeedback,
    }),
  );
  window.location.hash = redirect;
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
