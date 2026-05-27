import Vue from 'vue';
import Vuex, { Commit } from 'vuex';
import * as api from '../api';
import * as ui from '../ui';
import { messages, types } from '../api_types';
import {
  ContestTab,
  ContestOrder,
  ContestFilter,
} from '../components/arena/ContestList.vue';

Vue.use(Vuex);

export interface UrlParams {
  page: number;
  tab_name: ContestTab;
  query: string;
  sort_order: ContestOrder;
  filter: ContestFilter;
  replaceState?: boolean;
}

export interface ContestState {
  // The map of contest lists.
  contests: Record<string, types.ContestListItem[]>;
  countContests: Record<string, number>;
  cache: Record<
    string,
    messages.ContestListResponse | ContestListAllTabsResponse
  >;
  loading: boolean;
}

export interface NamedContestListRequest {
  name: string;
  requestParams: UrlParams;
}

interface NamedContestListResponse {
  name: string;
  cacheKey: string;
  response: messages.ContestListResponse;
  page?: number;
}

type ContestListAllTabsRequest = {
  page: number;
  query: string;
  sort_order: ContestOrder;
  filter: ContestFilter;
};

type ContestListAllTabsTabResponse = {
  number_of_results: number;
  results: types.ContestListItem[];
};

type ContestListAllTabsResponse = {
  current: ContestListAllTabsTabResponse;
  past: ContestListAllTabsTabResponse;
  future: ContestListAllTabsTabResponse;
};

type ContestListAllTabsServerResponse = {
  current: {
    number_of_results: number;
    results: Array<
      Omit<
        types.ContestListItem,
        'finish_time' | 'last_updated' | 'original_finish_time' | 'start_time'
      > & {
        finish_time: number;
        last_updated: number;
        original_finish_time: number;
        start_time: number;
      }
    >;
  };
  past: {
    number_of_results: number;
    results: Array<
      Omit<
        types.ContestListItem,
        'finish_time' | 'last_updated' | 'original_finish_time' | 'start_time'
      > & {
        finish_time: number;
        last_updated: number;
        original_finish_time: number;
        start_time: number;
      }
    >;
  };
  future: {
    number_of_results: number;
    results: Array<
      Omit<
        types.ContestListItem,
        'finish_time' | 'last_updated' | 'original_finish_time' | 'start_time'
      > & {
        finish_time: number;
        last_updated: number;
        original_finish_time: number;
        start_time: number;
      }
    >;
  };
};

const listAllTabs = api.apiCall<
  ContestListAllTabsRequest,
  ContestListAllTabsServerResponse,
  ContestListAllTabsResponse
>('/api/contest/listAllTabs/', (response) => {
  const mapTabResponse = (
    tabResponse: ContestListAllTabsServerResponse['current'],
  ): ContestListAllTabsTabResponse => ({
    number_of_results: tabResponse.number_of_results,
    results: tabResponse.results.map((contest) => ({
      ...contest,
      finish_time: new Date(contest.finish_time * 1000),
      last_updated: new Date(contest.last_updated * 1000),
      original_finish_time: new Date(contest.original_finish_time * 1000),
      start_time: new Date(contest.start_time * 1000),
    })),
  });

  return {
    current: mapTabResponse(response.current),
    past: mapTabResponse(response.past),
    future: mapTabResponse(response.future),
  };
});

const pendingAllTabsRequests: Partial<
  Record<string, Promise<ContestListAllTabsResponse>>
> = {};

function isAllTabsResponse(
  cached: messages.ContestListResponse | ContestListAllTabsResponse,
): cached is ContestListAllTabsResponse {
  return (
    cached !== null &&
    typeof cached === 'object' &&
    'current' in cached &&
    'past' in cached &&
    'future' in cached
  );
}

export const contestStoreConfig = {
  state: {
    contests: {},
    countContests: {},
    cache: {},
    loading: false,
  },
  mutations: {
    setLoading(state: ContestState, isLoading: boolean) {
      state.loading = isLoading;
    },

    updateAll(state: ContestState, payloadContests: types.TimeTypeContests) {
      state.contests = { ...state.contests, ...payloadContests };
    },
    updateAllCounts(
      state: ContestState,
      payloadCountContests: { [key: string]: number },
    ) {
      state.countContests = { ...state.countContests, ...payloadCountContests };
    },
    updateList(
      state: ContestState,
      { name, cacheKey, response, page }: NamedContestListResponse,
    ) {
      // Get the existing contests for this tab (or initialize as empty array)
      // If page is 1, we start fresh (replace), otherwise we append.
      const existingContests =
        (page || 1) === 1 ? [] : state.contests[name] || [];

      // Filter out duplicates by contest_id
      const newContests = response.results.filter(
        (newContest) =>
          !existingContests.some(
            (existing) => existing.contest_id === newContest.contest_id,
          ),
      );

      // Append new contests to the existing list
      Vue.set(state.contests, name, [...existingContests, ...newContests]);
      Vue.set(state.countContests, name, response.number_of_results);

      // Update cache with the full response
      Vue.set(state.cache, cacheKey, {
        results: response.results,
        number_of_results: response.number_of_results,
      });
    },
    applyAllTabsResponse(
      state: ContestState,
      {
        response,
        page,
      }: {
        response: ContestListAllTabsResponse;
        page: number;
      },
    ) {
      const tabs: Array<keyof ContestListAllTabsResponse> = [
        'current',
        'past',
        'future',
      ];
      for (const tab of tabs) {
        const tabResponse = response[tab];
        const existingContests = page === 1 ? [] : state.contests[tab] || [];
        const newContests = tabResponse.results.filter(
          (newContest: types.ContestListItem) =>
            !existingContests.some(
              (existing: types.ContestListItem) =>
                existing.contest_id === newContest.contest_id,
            ),
        );
        Vue.set(state.contests, tab, [...existingContests, ...newContests]);
        Vue.set(state.countContests, tab, tabResponse.number_of_results);
      }
    },
    cacheAllTabsList(
      state: ContestState,
      payload: {
        cacheKey: string;
        response: ContestListAllTabsResponse;
        requestParams: UrlParams;
      },
    ) {
      Vue.set(state.cache, payload.cacheKey, payload.response);
      const tabs = [ContestTab.Current, ContestTab.Past, ContestTab.Future];
      for (const tab of tabs) {
        const tabCacheKey = generateCacheKey({
          ...payload.requestParams,
          tab_name: tab,
        });
        Vue.set(state.cache, tabCacheKey, payload.response[tab]);
      }
    },
  },
  actions: {
    fetchContestListAllTabs(
      { commit, state }: { commit: Commit; state: ContestState },
      payload: { requestParams: UrlParams },
    ) {
      const cacheKey = generateAllTabsCacheKey(payload.requestParams);
      if (pendingAllTabsRequests[cacheKey]) {
        return pendingAllTabsRequests[cacheKey];
      }
      const cached = state.cache[cacheKey];
      if (cached && isAllTabsResponse(cached)) {
        commit('applyAllTabsResponse', {
          response: cached,
          page: payload.requestParams.page,
        });
        return Promise.resolve();
      }
      commit('setLoading', true);
      const p = payload.requestParams;
      const listParams = {
        page: p.page,
        query: p.query,
        sort_order: p.sort_order,
        filter: p.filter,
      };
      pendingAllTabsRequests[cacheKey] = listAllTabs(listParams)
        .then((response: ContestListAllTabsResponse) => {
          commit('cacheAllTabsList', {
            cacheKey,
            response,
            requestParams: payload.requestParams,
          });
          commit('applyAllTabsResponse', {
            response,
            page: payload.requestParams.page,
          });
          return response;
        })
        .catch((err: any) => {
          ui.apiError(err);
          throw err;
        })
        .finally(() => {
          commit('setLoading', false);
          delete pendingAllTabsRequests[cacheKey];
        });
      return pendingAllTabsRequests[cacheKey];
    },
    fetchContestList(
      {
        commit,
        state,
        dispatch,
      }: {
        commit: Commit;
        state: ContestState;
        dispatch: (action: string, payload?: unknown) => Promise<unknown>;
      },
      payload: NamedContestListRequest,
    ) {
      if (payload.requestParams.page === 1) {
        return dispatch('fetchContestListAllTabs', {
          requestParams: payload.requestParams,
        });
      }
      const cacheKey = generateCacheKey(payload.requestParams);
      const cachedList = state.cache[cacheKey];
      if (cachedList && !isAllTabsResponse(cachedList)) {
        commit('updateList', {
          name: payload.name,
          cacheKey,
          response: cachedList,
          page: payload.requestParams.page,
        });
        return;
      }
      commit('setLoading', true);
      return api.Contest.list(payload.requestParams)
        .then((response) => {
          commit('updateList', {
            name: payload.name,
            cacheKey,
            response,
            page: payload.requestParams.page,
          });
        })
        .catch(ui.apiError)
        .finally(() => {
          commit('setLoading', false);
        });
    },
  },
};

function generateCacheKey(params: UrlParams) {
  return `${params.tab_name}-${params.filter}-${params.sort_order}-${params.query}-${params.page}`;
}

function generateAllTabsCacheKey(params: UrlParams) {
  return `all-tabs-${params.filter}-${params.sort_order}-${params.query}-${params.page}`;
}

export default new Vuex.Store<ContestState>(contestStoreConfig);
