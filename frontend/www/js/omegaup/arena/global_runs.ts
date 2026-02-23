import Vue from 'vue';

import arena_Runs, { PopupDisplayed } from '../components/arena/Runs.vue';
import * as api from '../api';
import T from '../lang';
import { OmegaUp } from '../omegaup';
import * as ui from '../ui';
import * as time from '../time';
import { runsStore } from './runsStore';
import {
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
      offset: 0,
      loading: false,
      endOfResults: false,
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
          offset: this.offset,
          loading: this.loading,
          endOfResults: this.endOfResults,
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
                updateRunFallback({ run });
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
                updateRunFallback({ run });
              })
              .catch(ui.ignoreError);
          },
          'filter-changed': () => {
            this.offset = 0;
            this.endOfResults = false;
            runsStore.commit('setTotalRuns', 0);
            refreshRuns();
          },
          'fetch-more-data': () => {
            if (this.loading || this.endOfResults) {
              return;
            }
            this.loading = true;
            refreshRuns(this.offset);
            if (this.offset * 10 > runsStore.state.totalRuns) {
              this.endOfResults = true;
            }
            this.loading = false;
            this.offset += 1;
          },
          rejudge: (run: types.Run) => {
            api.Run.rejudge({ run_alias: run.guid, debug: false })
              .then(() => {
                run.status = 'rejudging';
                updateRunFallback({ run });
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

  function refreshRuns(offset = 0): void {
    api.Run.list({
      show_all: true,
      offset: offset,
      rowcount: 10,
      verdict: runsStore.state.filters?.verdict,
      language: runsStore.state.filters?.language,
      username: runsStore.state.filters?.username,
      status: runsStore.state.filters?.status,
    })
      .then(time.remoteTimeAdapter)
      .then((response) => {
        if (offset === 0) {
          onRefreshRuns({ runs: response.runs, totalRuns: response.totalRuns });
        } else if (response.runs.length !== 0) {
          runsStore.commit('addRuns', response.runs);
        }
        runsStore.commit('setTotalRuns', response.totalRuns);
      })
      .catch(ui.apiError);
  }

  refreshRuns();
});
