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
    ? parseInt(window.location.hash.substr(1))
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
          'get-chunk': (page: number, pageSize: number, query: string) => {
            api.Contest.list({
              page: page,
              page_size: pageSize,
              query: query,
            })
              .then((data) => {
                const newChunk: types.ContestList = {
                  future: [],
                  past: [],
                  current: [],
                };
                const today = new Date();
                data.results.forEach((contest) => {
                  if (today < contest.start_time) {
                    newChunk.future.push(contest);
                  } else if (today > contest.finish_time) {
                    newChunk.past.push(contest);
                  } else {
                    newChunk.current.push(contest);
                  }
                });
                this.contests = newChunk;
              })
              .catch(ui.apiError);
          },
        },
      });
    },
  });
});
