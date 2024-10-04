import Vue from 'vue';
import Vuex, { Commit } from 'vuex';
import * as api from '../api';
import { messages, types } from '../api_types';

Vue.use(Vuex);

export interface ContestState {
  // The map of contest lists.
  contests: types.TimeTypeContests;
  countContests: { [key: string]: number };
}

export interface NamedContestListRequest {
  name: string;
  requestParams: messages.ContestListRequest;
}

interface NamedContestListResponse {
  name: string;
  response: messages.ContestListResponse;
}

export const contestStoreConfig = {
  state: {
    contests: {},
    countContests: {},
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
      { name, response }: NamedContestListResponse,
    ) {
      Vue.set(state.contests, name, response);
    },
  },
  actions: {
    fetchContestList(
      { commit }: { commit: Commit },
      payload: NamedContestListRequest,
    ) {
      api.Contest.list(payload.requestParams).then((response) => {
        commit('updateList', {
          name: payload.name,
          response: response.results,
        });
      });
    },
  },
};

export default new Vuex.Store<ContestState>(contestStoreConfig);
