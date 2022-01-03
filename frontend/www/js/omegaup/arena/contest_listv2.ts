import { OmegaUp } from '../omegaup';
import * as time from '../time';
import { types } from '../api_types';
import * as api from '../api';
import * as ui from '../ui';
import Vue from 'vue';
import arena_ContestList, {
  ContestTab,
} from '../components/arena/ContestListv2.vue';

OmegaUp.on('ready', () => {
  time.setSugarLocale();
  const payload = types.payloadParsers.ContestListv2Payload();
  const tab: ContestTab = window.location.hash
    ? window.location.hash.slice(1)
    : ContestTab.Current;
  new Vue({
    el: '#main-container',
    components: { 'omegaup-arena-contestlist': arena_ContestList },
    data: () => ({
      query: payload.query,
      contests: payload.contests,
    }),
    render: function (createElement) {
      return createElement('omegaup-arena-contestlist', {
        props: {
          contests: this.contests,
          query: this.query,
          tab,
        },
        on: {
          'get-chunk': (
            page: number,
            pageSize: number,
            query: string,
            tab: ContestTab,
          ) => {
            api.Contest.list({
              page: page,
              page_size: pageSize,
              query: query,
              tab_name: tab,
            })
              .then((data) => {
                if (!data.number_of_results) {
                  return;
                }
                switch (tab) {
                  case ContestTab.Current:
                    this.contests.current = data.results.slice();
                    break;
                  case ContestTab.Past:
                    this.contests.past = data.results.slice();
                    break;
                  case ContestTab.Future:
                    this.contests.future = data.results.slice();
                    break;
                }
              })
              .catch(ui.apiError);
          },
        },
      });
    },
  });
});
