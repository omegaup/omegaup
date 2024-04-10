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
      page: payload.page,
      submissions: payload.submissions,
      loading: false, // Flag to prevent multiple simultaneous requests
      endOfResults: false, // Flag to indicate if all results have been loaded
    }),
    methods: {
      fetchMoreData() {
        if (this.loading || this.endOfResults) return;
        // this.loading = true;
        this.submissions = [ ...this.submissions, ...this.submissions];     
        // this.$set(this.submissions, 0,5)
        // Vue.set(this, 'submissions', this.submissions);
        // console.log(this.submissions.length);
        // api.User.list({ query: '', page: this.page + 1 }) // Fetch the next page of results
        //   .then(({results}) => {
        //     if (results.length === 0) {
        //       this.endOfResults = true; // No more results available
        //     } else {
        //       this.page++;
        //       console.log(results);
        //       this.submissions = [...this.submissions,...this.submissions ] // Append new results to existing ones
        //     }
        //   })
        //   .catch(ui.apiError)
          // .finally(() => {
            // this.loading = false;
        //   });
      },
    },
    render: function (createElement) {
      return createElement('omegaup-submissions-list', {
        props: {
          includeUser: payload.includeUser,
          page: payload.page,
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
          'fetch-more-data': this.fetchMoreData,
        },
      });
    },
  });
});
