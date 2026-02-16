import Vue from 'vue';
import Vuex, { Commit } from 'vuex';
import * as api from '../api';
import { messages, types } from '../api_types';
import { UrlParams } from '../components/arena/ContestListv2.vue';

Vue.use(Vuex);

export interface ContestState {
  // The map of contest lists.
  contests: Record<string, types.ContestListItem[]>;
  countContests: Record<string, number>;
  cache: Record<string, messages.ContestListResponse>;
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
      { name, cacheKey, response }: NamedContestListResponse,
    ) {
      // Get the existing contests for this tab (or initialize as empty array)
      const existingContests = state.contests[name] || [];

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
  },
  actions: {
    fetchContestList(
      { commit, state }: { commit: Commit; state: ContestState },
      payload: NamedContestListRequest,
    ) {
      const cacheKey = generateCacheKey(payload.requestParams);
      if (state.cache[cacheKey]) {
        commit('updateList', {
          name: payload.name,
          cacheKey,
          response: state.cache[cacheKey],
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
          });
        })
        .finally(() => {
          commit('setLoading', false);
        });
    },
  },
};

function generateCacheKey(params: UrlParams) {
  return `${params.tab_name}-${params.filter}-${params.sort_order}-${params.query}-${params.page}`;
}

export default new Vuex.Store<ContestState>(contestStoreConfig);
