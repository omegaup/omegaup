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
    }),
    render: function (createElement) {
      return createElement('omegaup-submissions-list', {
        props: {
          page: payload.page,
          length: payload.length,
          pagerItems: payload.pagerItems,
          includeUser: payload.includeUser,
          submissions: payload.submissions,
          totalRows: payload.totalRows,
          searchResultUsers: this.searchResultUsers,
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
        },
      });
    },
  });
});
