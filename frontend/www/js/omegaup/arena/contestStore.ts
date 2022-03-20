import Vue from 'vue';
import Vuex, { Commit } from 'vuex';
import * as api from '../api';
import { messages, types } from '../api_types';

Vue.use(Vuex);

export interface ContestState {
  // The map of contest lists.
  contests: types.TimeTypeContests;
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
    contests: types.TimeTypeContests,
  },
  mutations: {
    updateAll(state: ContestState, payloadContests: types.TimeTypeContests) {
      state.contests = { ...state.contests, ...payloadContests };
    },
    updateList(
      state: ContestState,
      { name, response }: NamedContestListResponse,
    ) {
      Vue.set(state.contests, name, response);
    },
  },
  actions: {
    getContestList(
      { commit }: { commit: Commit },
      payload: NamedContestListRequest,
    ) {
      api.Contest.list(payload.requestParams).then((response) => {
        commit('updateList', { name: payload.name, response });
      });
    },
  },
};

export default new Vuex.Store<ContestState>(contestStoreConfig);
