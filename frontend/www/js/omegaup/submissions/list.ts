import submissions_List from '../components/submissions/List.vue';
import { OmegaUp } from '../omegaup';
import { types } from '../api_types';
import * as ui from '../ui';
import * as api from '../api';
import Vue from 'vue';

OmegaUp.on('ready', () => {
  const payload = types.payloadParsers.SubmissionsListPayload();

  new Vue({
    el: '#main-container',
    components: {
      'omegaup-submissions-list': submissions_List,
    },
    data: () => ({
      searchResultUsers: [] as types.ListItem[],
      page: 1,
      username: payload.username,
      submissions: payload.submissions,
      loading: false, // Flag to prevent multiple simultaneous requests
      endOfResults: false, // Flag to indicate if all results have been loaded
    }),
    render: function (createElement) {
      return createElement('omegaup-submissions-list', {
        props: {
          includeUser: payload.includeUser,
          page: this.page,
          submissions: this.submissions,
          searchResultUsers: this.searchResultUsers,
          loading: this.loading,
          endOfResults: this.endOfResults,
        },

        on: {
          'update-search-result-users': (query: string) => {
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
          'fetch-more-data': () => {
            if (this.loading || this.endOfResults) return;
            this.loading = true;
            api.Submission.list({
              username: this.username,
              page: this.page + 1,
            })
              .then(({ submissions }) => {
                if (submissions === null || submissions.length === 0) {
                  this.endOfResults = true;
                } else {
                  this.page++;
                  this.submissions = [...this.submissions, ...submissions];
                }
              })
              .catch((error) => {
                this.endOfResults = true;
                ui.apiError(error);
              })
              .finally(() => {
                this.loading = false;
              });
          },
        },
      });
    },
  });
});
