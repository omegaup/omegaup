import Vue from 'vue';
import * as api from '../api';
import * as ui from '../ui';
import * as time from '../time';
import { types } from '../api_types';
import { myRunsStore, runsStore } from './runsStore';
import { OmegaUp } from '../omegaup';
import JSZip from 'jszip';
import T from '../lang';

interface RunSubmit {
  classname: string;
  username: string;
  guid: string;
  submitDelay: number;
  language: string;
  problemAlias: string;
}

interface SubmissionResponse {
  request: SubmissionRequest;
  runDetails: types.RunDetails;
}

export interface SubmissionRequest {
  guid: string;
  hash?: string;
  isAdmin: boolean;
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
    guid,
    submit_delay: submitDelay,
    username,
    classname,
    country: 'xx',
    execution: 'EXECUTION_JUDGE_ERROR',
    status: 'new',
    status_memory: 'MEMORY_NOT_AVAILABLE',
    status_runtime: 'RUNTIME_NOT_AVAILABLE',
    alias: problemAlias,
    time: new Date(),
    output: 'OUTPUT_INTERRUPTED',
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
  alert(error ?? run);
  if (errorname) {
    ui.reportEvent('submission', 'submit-fail', errorname);
  }
}

export function showSubmission({
  request,
  runDetails,
}: SubmissionResponse): types.RunDetails {
  if (runDetails.show_diff === 'none' || !request.isAdmin) {
    return displayRunDetails({
      request,
      runDetails,
    });
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
          runDetails.show_diff === 'examples' &&
          relativePath.indexOf('sample/') === 0
        ) {
          return;
        }
        result.cases.push(basename);
        const zipFile = zip.file(zipEntry.name);
        if (zipFile) {
          result.promises.push(zipFile.async('text'));
        }
      });
      return result;
    })
    .then((response) => {
      Promise.allSettled(response.promises).then((results) => {
        results.forEach((result: any, index: number) => {
          if (runDetails.cases[response.cases[index]]) {
            runDetails.cases[response.cases[index]].contestantOutput =
              result.value;
          }
        });
      });
      return displayRunDetails({ request, runDetails });
    })
    .catch(ui.apiError);
  return displayRunDetails({
    request,
    runDetails,
  });
}

function numericSort<T extends { [key: string]: any }>(key: string) {
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
}

function displayRunDetails({
  request,
  runDetails,
}: SubmissionResponse): types.RunDetails {
  let sourceHTML,
    sourceLink = false;
  if (runDetails.source?.indexOf('data:') === 0) {
    sourceLink = true;
    sourceHTML = runDetails.source;
  } else if (runDetails.source == 'lockdownDetailsDisabled') {
    sourceHTML =
      (typeof sessionStorage !== 'undefined' &&
        sessionStorage.getItem(`run:${request.guid}`)) ||
      T.lockdownDetailsDisabled;
  } else {
    sourceHTML = runDetails.source;
  }

  const detailsGroups = runDetails.details && runDetails.details.groups;

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

  return {
    ...runDetails,
    ...{
      logs: runDetails.logs || '',
      judged_by: runDetails.judged_by || '',
      source: sourceHTML,
      source_link: sourceLink,
      source_url: window.URL.createObjectURL(
        new Blob([runDetails.source || ''], { type: 'text/plain' }),
      ),
      source_name: `Main.${runDetails.language}`,
      groups,
      show_diff: request.isAdmin ? runDetails.show_diff : 'none',
      feedback: runDetails.feedback,
    },
  };
}

export function updateRun({ run }: { run: types.Run }): void {
  trackRun({ run });

  if (run.status != 'ready') {
    updateRunFallback({ run });
    return;
  }
}

export function updateRunFallback({ run }: { run: types.Run }): void {
  setTimeout(() => {
    api.Run.status({ run_alias: run.guid, username: run.username })
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

export function onRefreshRuns({
  runs,
  totalRuns,
}: {
  runs: types.Run[];
  totalRuns: number;
}): void {
  runsStore.commit('setTotalRuns', totalRuns);
  runsStore.commit('clear');
  for (const run of runs) {
    trackRun({ run });
  }
}
