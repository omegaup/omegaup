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
    }),
    methods: {
      onCompare({
        username1,
        username2,
      }: {
        username1?: string;
        username2?: string;
      }): void {
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
    },
    render: function (createElement) {
      return createElement('omegaup-user-compare', {
        props: {
          user1: this.user1,
          user2: this.user2,
          initialUsername1: payload.username1,
          initialUsername2: payload.username2,
          isLoading: this.isLoading,
        },
        on: {
          compare: (this as any).onCompare,
        },
      });
    },
  });
});
