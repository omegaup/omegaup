import Vue from 'vue';

import arena_Runs, { PopupDisplayed } from '../components/arena/Runs.vue';
import * as api from '../api';
import T from '../lang';
import { OmegaUp } from '../omegaup';
import * as ui from '../ui';
import * as time from '../time';
import { runsStore } from './runsStore';
import {
  Actions,
  onRefreshRuns,
  showSubmission,
  SubmissionRequest,
  updateRunFallback,
} from './submissions';
import { types } from '../api_types';
import { getOptionsFromLocation, getProblemAndRunDetails } from './location';

OmegaUp.on('ready', async () => {
  const { guid, popupDisplayed } = getOptionsFromLocation(window.location.hash);
  const searchResultEmpty: types.ListItem[] = [];
  let runDetails: null | types.RunDetails = null;
  try {
    ({ runDetails } = await getProblemAndRunDetails({
      location: window.location.hash,
    }));
  } catch (e: any) {
    ui.apiError(e);
  }
  new Vue({
    el: '#main-container',
    components: {
      'omegaup-arena-runs': arena_Runs,
    },
    data: () => ({
      searchResultUsers: searchResultEmpty,
      searchResultProblems: searchResultEmpty,
      popupDisplayed,
      guid,
      runDetailsData: runDetails,
    }),
    render: function (createElement) {
      return createElement('omegaup-arena-runs', {
        props: {
          contestAlias: 'admin',
          popupDisplayed: this.popupDisplayed,
          runs: runsStore.state.runs,
          showContest: true,
          showProblem: true,
          showDetails: true,
          showDisqualify: true,
          showPager: true,
          showRejudge: true,
          showUser: true,
          guid: this.guid,
          searchResultUsers: this.searchResultUsers,
          searchResultProblems: this.searchResultProblems,
          runDetailsData: this.runDetailsData,
          totalRuns: runsStore.state.totalRuns,
        },
        on: {
          details: (request: SubmissionRequest) => {
            api.Run.details({ run_alias: request.guid })
              .then((runDetails) => {
                this.runDetailsData = showSubmission({ request, runDetails });
                if (request.hash) {
                  window.location.hash = request.hash;
                }
              })
              .catch((error) => {
                ui.apiError(error);
                this.popupDisplayed = PopupDisplayed.None;
              });
          },
          requalify: (run: types.Run) => {
            api.Run.requalify({ run_alias: run.guid })
              .then(() => {
                run.type = 'normal';
                updateRunFallback({ run, action: Actions.Requalify });
              })
              .catch(ui.ignoreError);
          },
          disqualify: ({ run }: { run: types.Run }) => {
            if (!window.confirm(T.runDisqualifyConfirm)) {
              return;
            }
            api.Run.disqualify({ run_alias: run.guid })
              .then(() => {
                run.type = 'disqualified';
                updateRunFallback({ run, action: Actions.Disqualify });
              })
              .catch(ui.ignoreError);
          },
          'filter-changed': () => {
            refreshRuns();
          },
          rejudge: (run: types.Run) => {
            api.Run.rejudge({ run_alias: run.guid, debug: false })
              .then((response) => {
                run.status = 'rejudging';
                run.version = response.version;
                updateRunFallback({ run, action: Actions.Rejudge });
              })
              .catch(ui.ignoreError);
          },
          'update-search-result-users': ({ query }: { query: string }) => {
            api.User.list({ query })
              .then(({ results }) => {
                this.searchResultUsers = results.map(
                  ({ key, value }: types.ListItem) => ({
                    key,
                    value: `${ui.escape(key)} (<strong>${ui.escape(
                      value,
                    )}</strong>)`,
                  }),
                );
              })
              .catch(ui.apiError);
          },
          'update-search-result-users-contest': ({
            query,
            contestAlias,
          }: {
            query: string;
            contestAlias: string;
          }) => {
            api.Contest.searchUsers({ query, contest_alias: contestAlias })
              .then(({ results }) => {
                this.searchResultUsers = results.map(
                  ({ key, value }: types.ListItem) => ({
                    key,
                    value: `${ui.escape(key)} (<strong>${ui.escape(
                      value,
                    )}</strong>)`,
                  }),
                );
              })
              .catch(ui.apiError);
          },
          'update-search-result-problems': (query: string) => {
            api.Problem.listForTypeahead({
              query,
              search_type: 'all',
            })
              .then((data) => {
                this.searchResultProblems = data.results.map(
                  ({ key, value }, index) => ({
                    key,
                    value: `${String(index + 1).padStart(2, '0')}.- ${ui.escape(
                      value,
                    )} (<strong>${ui.escape(key)}</strong>)`,
                  }),
                );
              })
              .catch(ui.apiError);
          },
          'reset-hash': () => {
            history.replaceState({}, '', '#');
          },
        },
      });
    },
  });

  function refreshRuns(): void {
    api.Run.list({
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
        onRefreshRuns({ runs: response.runs, totalRuns: response.totalRuns });
      })
      .catch(ui.apiError);
  }

  refreshRuns();
  setInterval(() => {
    refreshRuns();
  }, 5 * 60 * 1000);
});
