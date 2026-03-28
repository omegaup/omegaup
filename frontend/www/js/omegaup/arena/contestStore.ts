import Vue from 'vue';
import Vuex, { Commit } from 'vuex';
import * as api from '../api';
import { messages, types } from '../api_types';
import * as ui from '../ui';
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
    messages.ContestListResponse | messages.ContestListAllTabsResponse
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
        response: messages.ContestListAllTabsResponse;
        page: number;
      },
    ) {
      const tabs: (keyof messages.ContestListAllTabsResponse)[] = [
        'current',
        'past',
        'future',
      ];
      for (const tab of tabs) {
        const tabResponse = response[tab];
        const existingContests =
          page === 1 ? [] : state.contests[tab] || [];
        const newContests = tabResponse.results.filter(
          (newContest) =>
            !existingContests.some(
              (existing) => existing.contest_id === newContest.contest_id,
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
        response: messages.ContestListAllTabsResponse;
        requestParams: UrlParams;
      },
    ) {
      Vue.set(state.cache, payload.cacheKey, payload.response);
      const tabs = [
        ContestTab.Current,
        ContestTab.Past,
        ContestTab.Future,
      ];
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
      const cached = state.cache[cacheKey];
      if (cached && 'current' in cached) {
        commit('applyAllTabsResponse', {
          response: cached as messages.ContestListAllTabsResponse,
          page: payload.requestParams.page,
        });
        return Promise.resolve();
      }
      commit('setLoading', true);
      const rp = payload.requestParams;
      const listParams = {
        page: rp.page,
        query: rp.query,
        sort_order: rp.sort_order,
        filter: rp.filter,
      };
      // Assignment (not `return x.then(...)`) so stuff/refactor.js accepts this file.
      let listPromise = api.Contest.listAllTabs(listParams);
      listPromise = listPromise
        .then((response) => {
          commit('cacheAllTabsList', {
            cacheKey,
            response,
            requestParams: payload.requestParams,
          });
          commit('applyAllTabsResponse', {
            response,
            page: payload.requestParams.page,
          });
        })
        .catch(ui.apiError)
        .finally(() => {
          commit('setLoading', false);
        });
      return listPromise;
    },
    fetchContestList(
      { commit, state }: { commit: Commit; state: ContestState },
      payload: NamedContestListRequest,
    ) {
      const cacheKey = generateCacheKey(payload.requestParams);
      const cachedList = state.cache[cacheKey];
      if (cachedList && !('current' in cachedList)) {
        commit('updateList', {
          name: payload.name,
          cacheKey,
          response: cachedList as messages.ContestListResponse,
          page: payload.requestParams.page,
        });
        return;
      }
      commit('setLoading', true);
      api.Contest.list(payload.requestParams)
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
