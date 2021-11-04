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
      searchResultUsers: [] as types.ListItem[],
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
          runDetailsData: this.runDetailsData,
        },
        on: {
          details: (request: SubmissionRequest) => {
            api.Run.details({ run_alias: request.guid })
              .then((runDetails) => {
                this.runDetailsData = showSubmission({ request, runDetails });
                window.location.hash = request.hash;
              })
              .catch((error) => {
                ui.apiError(error);
                this.popupDisplayed = PopupDisplayed.None;
              });
          },
          disqualify: (run: types.Run) => {
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
            refreshRuns();
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
        onRefreshRuns({ runs: response.runs });
      })
      .catch(ui.apiError);
  }

  refreshRuns();
  setInterval(() => {
    refreshRuns();
  }, 5 * 60 * 1000);
});
