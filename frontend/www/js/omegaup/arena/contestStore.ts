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
  },
  mutations: {
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
      Vue.set(state.contests, name, response.results);
      Vue.set(state.countContests, name, response.number_of_results);

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
      api.Contest.list(payload.requestParams).then((response) => {
        commit('updateList', {
          name: payload.name,
          cacheKey,
          response,
        });
      });
    },
  },
};

function generateCacheKey(params: UrlParams) {
  return `${params.tab_name}-${params.filter}-${params.sort_order}-${params.query}-${params.page}`;
}

export default new Vuex.Store<ContestState>(contestStoreConfig);
