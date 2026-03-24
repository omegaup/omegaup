import { OmegaUp } from '../omegaup';
import * as time from '../time';
import { types } from '../api_types';
import Vue from 'vue';
import arena_ContestList, {
  ContestTab,
  ContestOrder,
  ContestFilter,
} from '../components/arena/ContestList.vue';
import contestStore, { UrlParams } from './contestStore';

OmegaUp.on('ready', () => {
  time.setSugarLocale();
  const payload = types.payloadParsers.ContestListv2Payload();
  contestStore.commit('updateAll', payload.contests);
  contestStore.commit('updateAllCounts', payload.countContests);
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
      if (filterParam === 'signedup') {
        filter = ContestFilter.SignedUp;
      } else if (filterParam === 'recommended') {
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
    }),
    render: function (createElement) {
      return createElement('omegaup-arena-contestlist', {
        props: {
          contests: contestStore.state.contests,
          countContests: contestStore.state.countContests,
          query: this.query,
          tab,
          page,
          sortOrder,
          filter,
          pageSize: payload.pageSize,
          loading: contestStore.state.loading,
        },
        on: {
          'fetch-page': async ({
            params,
            urlObj,
            shouldUpdateUrl = true,
          }: {
            params: UrlParams;
            urlObj: URL;
            shouldUpdateUrl?: boolean;
          }) => {
            for (const [key, value] of Object.entries(params)) {
              if (value) {
                urlObj.searchParams.set(key, String(value));
              } else {
                urlObj.searchParams.delete(key);
              }
            }
            if (shouldUpdateUrl) {
              window.history.pushState({}, '', urlObj);
            }
            await contestStore.dispatch('fetchContestList', {
              requestParams: params,
              name: params.tab_name,
            });
          },
        },
      });
    },
  });
});
