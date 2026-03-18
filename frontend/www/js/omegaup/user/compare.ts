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
      username1: payload.username1,
      username2: payload.username2,
      isLoading: false,
      searchResultUsers1: [] as types.ListItem[],
      searchResultUsers2: [] as types.ListItem[],
      selectedUser1: null as types.ListItem | null,
      selectedUser2: null as types.ListItem | null,
    }),
    mounted() {
      // Preload usernames from URL params to show them as chips
      // Fetch user info to get proper name formatting (same as manual search)
      if (this.username1) {
        api.User.list({ query: this.username1 })
          .then(({ results }) => {
            const user = results.find((u) => u.key === this.username1);
            if (user) {
              this.selectedUser1 = {
                key: user.key,
                value: `${ui.escape(user.key)} (<strong>${ui.escape(
                  user.value,
                )}</strong>)`,
              };
            }
          })
          .catch(ui.apiError);
      }
      if (this.username2) {
        api.User.list({ query: this.username2 })
          .then(({ results }) => {
            const user = results.find((u) => u.key === this.username2);
            if (user) {
              this.selectedUser2 = {
                key: user.key,
                value: `${ui.escape(user.key)} (<strong>${ui.escape(
                  user.value,
                )}</strong>)`,
              };
            }
          })
          .catch(ui.apiError);
      }
    },
    render: function (createElement) {
      return createElement('omegaup-user-compare', {
        props: {
          user1: this.user1,
          user2: this.user2,
          username1: this.username1,
          username2: this.username2,
          isLoading: this.isLoading,
          searchResultUsers1: this.searchResultUsers1,
          searchResultUsers2: this.searchResultUsers2,
          selectedUser1: this.selectedUser1,
          selectedUser2: this.selectedUser2,
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

                // Exclude the user that is already selected in the other field
                // to avoid comparing a user against themselves
                const otherSelectedUserKey =
                  field === 'user1'
                    ? this.selectedUser2?.key
                    : this.selectedUser1?.key;

                const filteredResults = formattedResults.filter(
                  (user) => user.key !== otherSelectedUserKey,
                );

                if (field === 'user1') {
                  this.searchResultUsers1 = filteredResults;
                } else {
                  this.searchResultUsers2 = filteredResults;
                }
              })
              .catch(ui.apiError);
          },
          'update:selectedUser1': (user: types.ListItem | null) => {
            this.selectedUser1 = user;
          },
          'update:selectedUser2': (user: types.ListItem | null) => {
            this.selectedUser2 = user;
          },
        },
      });
    },
  });
});
