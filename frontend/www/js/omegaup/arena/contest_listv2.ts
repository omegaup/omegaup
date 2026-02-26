import Vue from 'vue';
import { types } from '../api_types';
import arena_ContestList, {
  ContestFilter,
  ContestOrder,
  ContestTab,
  UrlParams,
} from '../components/arena/ContestListv2.vue';
import { OmegaUp } from '../omegaup';
import * as time from '../time';
import contestStore from './contestStore';
import T from '../lang';
import * as ui from '../ui';

/**
 * Parses URL parameters and returns the contest filter state.
 */
function parseUrlState(): {
  tab: ContestTab;
  page: number;
  sortOrder: ContestOrder;
  filter: ContestFilter;
} {
  let tab: ContestTab = ContestTab.Current;
  let page: number = 1;
  let sortOrder: ContestOrder = ContestOrder.None;
  let filter: ContestFilter = ContestFilter.All;

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

  return { tab, page, sortOrder, filter };
}

OmegaUp.on('ready', () => {
  time.setSugarLocale();
  const payload = types.payloadParsers.ContestListv2Payload();
  contestStore.commit('updateAll', payload.contests);
  contestStore.commit('updateAllCounts', payload.countContests);

  const initialState = parseUrlState();

  // Handle browser back/forward button navigation
  const onPopState = () => {
    const state = parseUrlState();
    vueInstance.tab = state.tab;
    vueInstance.page = state.page;
    vueInstance.sortOrder = state.sortOrder;
    vueInstance.filter = state.filter;
  };

  // Handle hash changes (popstate doesn't always fire for hash-only changes)
  const onHashChange = () => {
    const state = parseUrlState();
    vueInstance.tab = state.tab;
  };

  const vueInstance = new Vue({
    el: '#main-container',
    components: { 'omegaup-arena-contestlist': arena_ContestList },
    data: () => ({
      query: payload.query,
      tab: initialState.tab,
      page: initialState.page,
      sortOrder: initialState.sortOrder,
      filter: initialState.filter,
    }),
    // eslint-disable-next-line vue/no-deprecated-destroyed-lifecycle
    beforeDestroy() {
      window.removeEventListener('popstate', onPopState);
      window.removeEventListener('hashchange', onHashChange);
    },
    render: function (createElement) {
      return createElement('omegaup-arena-contestlist', {
        props: {
          contests: contestStore.state.contests,
          countContests: contestStore.state.countContests,
          query: this.query,
          tab: this.tab,
          page: this.page,
          sortOrder: this.sortOrder,
          filter: this.filter,
          pageSize: payload.pageSize,
          loading: contestStore.state.loading,
        },
        on: {
          'fetch-page': async ({
            params,
            urlObj,
          }: {
            params: UrlParams;
            urlObj: URL;
          }) => {
            for (const [key, value] of Object.entries(params)) {
              if (key === 'replaceState') continue; // Don't add flag to URL
              if (value) {
                urlObj.searchParams.set(key, value.toString());
              } else {
                urlObj.searchParams.delete(key);
              }
            }
            // Use replaceState for browser navigation to avoid corrupting history
            if (params.replaceState) {
              window.history.replaceState({}, '', urlObj);
            } else {
              window.history.pushState({}, '', urlObj);
            }
            await contestStore.dispatch('fetchContestList', {
              requestParams: params,
              name: params.tab_name,
            });
          },
          'download-calendar': (alias: string) => {
            const url = `/api/contest/ical/?contest_alias=${encodeURIComponent(
              alias,
            )}`;
            const filename = `contest-${alias}.ics`;
            let blobUrl: string | null = null;

            ui.info(T.calendarDownloadStarted);

            fetch(url, { credentials: 'include' })
              .then((response) => {
                if (!response.ok) {
                  throw new Error(`HTTP error ${response.status}`);
                }
                return response.blob();
              })
              .then((blob) => {
                blobUrl = URL.createObjectURL(blob);
                const link = document.createElement('a');
                link.href = blobUrl;
                link.download = filename;
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
                ui.success(T.calendarDownloadSucceeded);
              })
              .catch((error) => {
                ui.error(T.calendarDownloadFailed);
                console.error('Calendar download failed:', error);
              })
              .finally(() => {
                // Delay revocation to allow Chrome to complete the download
                // See: https://stackoverflow.com/questions/30694453
                if (blobUrl) {
                  const urlToRevoke = blobUrl;
                  setTimeout(() => {
                    URL.revokeObjectURL(urlToRevoke);
                  }, 1000);
                }
              });
          },
          'subscribe-calendar': (alias: string) => {
            const calendarUrl = `/api/contest/ical/?contest_alias=${encodeURIComponent(
              alias,
            )}`;
            const httpsUrl = `${window.location.origin}${calendarUrl}`;
            const webcalUrl = httpsUrl.replace(/^https?:\/\//, 'webcal://');
            // Show message before navigating so it's visible
            ui.info(T.calendarSubscribeStarted);
            window.location.href = webcalUrl;
          },
        },
      });
    },
  });

  window.addEventListener('popstate', onPopState);
  window.addEventListener('hashchange', onHashChange);
});
