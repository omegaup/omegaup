import Vue from 'vue';
import * as api from '../api';
import { types } from '../api_types';
import user_CompareUsers from '../components/user/CompareUsers.vue';
import { OmegaUp } from '../omegaup';
import * as ui from '../ui';

OmegaUp.on('ready', () => {
  const payload = types.payloadParsers.UserComparePayload();

  new Vue({
    el: '#main-container',
    components: {
      'omegaup-user-compare': user_CompareUsers,
    },
    data: () => ({
      user1: payload.user1,
      user2: payload.user2,
      isLoading: false,
      searchResultUsers1: [] as types.ListItem[],
      searchResultUsers2: [] as types.ListItem[],
    }),
    render: function (createElement) {
      return createElement('omegaup-user-compare', {
        props: {
          user1: this.user1,
          user2: this.user2,
          initialUsername1: payload.username1,
          initialUsername2: payload.username2,
          isLoading: this.isLoading,
          searchResultUsers1: this.searchResultUsers1,
          searchResultUsers2: this.searchResultUsers2,
        },
        on: {
          compare: ({
            username1,
            username2,
          }: {
            username1?: string;
            username2?: string;
          }) => {
            this.isLoading = true;

            // Update URL
            const url = new URL(window.location.href);
            if (username1) {
              url.searchParams.set('username1', username1);
            } else {
              url.searchParams.delete('username1');
            }
            if (username2) {
              url.searchParams.set('username2', username2);
            } else {
              url.searchParams.delete('username2');
            }
            window.history.pushState({}, '', url.toString());

            api.User.compare({
              username1: username1 || undefined,
              username2: username2 || undefined,
            })
              .then((response) => {
                this.user1 = response.user1;
                this.user2 = response.user2;
              })
              .catch(ui.apiError)
              .finally(() => {
                this.isLoading = false;
              });
          },
          'update-search-result-users': ({
            query,
            field,
          }: {
            query: string;
            field: 'user1' | 'user2';
          }) => {
            api.User.list({ query })
              .then(({ results }) => {
                const formattedResults = results.map(
                  ({ key, value }: types.ListItem) => ({
                    key,
                    value: `${ui.escape(key)} (<strong>${ui.escape(
                      value,
                    )}</strong>)`,
                  }),
                );
                if (field === 'user1') {
                  this.searchResultUsers1 = formattedResults;
                } else {
                  this.searchResultUsers2 = formattedResults;
                }
              })
              .catch(ui.apiError);
          },
        },
      });
    },
  });
});
