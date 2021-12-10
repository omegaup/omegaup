import { OmegaUp } from '../omegaup';
import * as time from '../time';
import { types } from '../api_types';
import Vue from 'vue';
import arena_ContestList, {
  ContestsTab,
} from '../components/arena/ContestList.vue';

OmegaUp.on('ready', () => {
  time.setSugarLocale();
  const payload = types.payloadParsers.ContestListPayload();
  for (const contestList of Object.values(payload.contests)) {
    if (!contestList) {
      // The `participating` entry could be undefined.
      continue;
    }
    contestList.forEach((contest: types.ContestListItem) => {
      contest.finish_time = time.remoteDate(contest.finish_time);
      contest.last_updated = time.remoteDate(contest.last_updated);
      contest.start_time = time.remoteDate(contest.start_time);
    });
  }
  const locationHashTab = window.location.hash.substr(1);
  let selectedTab: string | null = null;
  for (const tab of Object.values(ContestsTab)) {
    if (locationHashTab === tab) {
      selectedTab = locationHashTab;
      break;
    }
  }
  if (!selectedTab) {
    for (const [timeType, contests] of Object.entries(payload.contests)) {
      if (contests.length > 0) {
        selectedTab = timeType;
        break;
      }
    }
  }

  new Vue({
    el: '#main-container',
    components: { 'omegaup-arena-contestlist': arena_ContestList },
    data: () => ({
      initialQuery: payload.query,
      isLogged: payload.isLogged,
      contests: payload.contests,
    }),
    render: function (createElement) {
      return createElement('omegaup-arena-contestlist', {
        props: {
          initialQuery: this.initialQuery,
          contests: this.contests,
          isLogged: this.isLogged,
          selectedTab,
        },
      });
    },
  });
});
