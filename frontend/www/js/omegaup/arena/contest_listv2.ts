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
          'get-next-chunk': (page: number, pageSize: number, query: string) => {
            api.Contest.list({
              page: page,
              page_size: pageSize,
              query: query,
            })
              .then((data) => {
                const currentContest: types.ContestListItem[] = [];
                const futureContest: types.ContestListItem[] = [];
                const pastContest: types.ContestListItem[] = [];

                const today = new Date();

                data.results.forEach((item) => {
                  const contest: types.ContestListItem = {
                    admission_mode: item.admission_mode,
                    alias: item.alias,
                    contest_id: item.contest_id,
                    contestants: item.contestants,
                    description: item.description,
                    finish_time: item.finish_time,
                    last_updated: item.last_updated,
                    organizer: item.organizer,
                    original_finish_time: item.original_finish_time,
                    partial_score: item.partial_score,
                    participating: item.participating,
                    problemset_id: item.problemset_id,
                    recommended: item.recommended,
                    start_time: item.start_time,
                    title: item.title,
                  };
                  if (item.start_time <= today && today <= item.finish_time) {
                    currentContest.push(contest);
                  } else if (today < item.start_time) {
                    futureContest.push(contest);
                  } else if (today > item.finish_time) {
                    pastContest.push(contest);
                  }
                });

                const newChunk: types.ContestList = {
                  current: currentContest,
                  future: futureContest,
                  past: pastContest,
                };
                this.contests = newChunk;
              })
              .catch(ui.apiError);
          },
        },
      });
    },
  });
});
