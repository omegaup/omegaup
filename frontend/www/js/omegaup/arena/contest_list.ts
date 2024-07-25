import { OmegaUp } from '../omegaup';
import * as time from '../time';
import * as api from '../api';
import * as ui from '../ui';
import { types } from '../api_types';
import Vue from 'vue';
import arena_ContestList, {
  ContestTab,
  ContestOrder,
  ContestFilter,
} from '../components/arena/ContestList.vue';

OmegaUp.on('ready', () => {
  time.setSugarLocale();
  const payload = types.payloadParsers.ContestListPayload();
  const contestIDs = payload.contests.map((contest) => contest.contest_id);
  if (contestIDs.length > 0) {
    api.Contest.getNumberOfContestants({ contest_ids: contestIDs })
      .then(({ response }) => {
        console.log(response);
        payload.contests.forEach((contest) => {
          contest.contestants = response[contest.contest_id] ?? 0;
        });
      })
      .catch(ui.apiError);
  }
  let tab: ContestTab = ContestTab.Current;
  const hash = window.location.hash ? window.location.hash.slice(1) : '';
  if (hash !== '') {
    switch (hash) {
      case 'future':
        tab = ContestTab.Future;
        break;
      case 'past':
        tab = ContestTab.Past;
        break;
      default:
        tab = ContestTab.Current;
        break;
    }
  }
  let page: number = 1;
  let sortOrder: ContestOrder = ContestOrder.None;
  let filter: ContestFilter = ContestFilter.All;
  const queryString = window.location.search;
  if (queryString) {
    const urlParams = new URLSearchParams(queryString);
    if (urlParams.get('sort_order')) {
      const sortOrderParam = urlParams.get('sort_order');
      if (sortOrderParam) {
        switch (sortOrderParam) {
          case 'title':
            sortOrder = ContestOrder.Title;
            break;
          case 'ends':
            sortOrder = ContestOrder.Ends;
            break;
          case 'duration':
            sortOrder = ContestOrder.Duration;
            break;
          case 'organizer':
            sortOrder = ContestOrder.Organizer;
            break;
          case 'contestants':
            sortOrder = ContestOrder.Contestants;
            break;
          case 'signedup':
            sortOrder = ContestOrder.SignedUp;
            break;
          default:
            sortOrder = ContestOrder.None;
            break;
        }
      }
    }
    if (urlParams.get('page')) {
      const pageParam = urlParams.get('page');
      if (pageParam) {
        page = parseInt(pageParam);
      }
    }
    if (urlParams.get('filter')) {
      const filterParam = urlParams.get('filter');
      if (filterParam === ContestFilter.SignedUp) {
        filter = ContestFilter.SignedUp;
      } else if (filterParam === ContestFilter.OnlyRecommended) {
        filter = ContestFilter.OnlyRecommended;
      }
    }
    if (urlParams.get('tab_name')) {
      const tabNameParam = urlParams.get('tab_name');
      if (tabNameParam) {
        switch (tabNameParam) {
          case 'future':
            tab = ContestTab.Future;
            break;
          case 'past':
            tab = ContestTab.Past;
            break;
          default:
            tab = ContestTab.Current;
            break;
        }
      }
    }
  }

  new Vue({
    el: '#main-container',
    components: { 'omegaup-arena-contestlist': arena_ContestList },
    data: () => ({
      query: payload.query,
      contests: payload.contests,
      countContests: payload.countContests,
    }),
    render: function (createElement) {
      return createElement('omegaup-arena-contestlist', {
        props: {
          contests: this.contests,
          countContests: this.countContests,
          query: this.query,
          tab,
          page,
          sortOrder,
          filter,
        },
        on: {
          'go-to-page': ({ path }: { path: string }) => {
            window.location.href = path;
          },
        },
      });
    },
  });
});
