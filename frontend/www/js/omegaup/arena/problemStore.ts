import Vue from 'vue';
import Vuex from 'vuex';
import { types } from '../api_types';

Vue.use(Vuex);

export interface ProblemState {
  // The mapping of problem alias to indexes on the problems array
  problems: Record<string, types.ProblemInfo>;
}

export const storeConfig = {
  state: {
    problems: {},
  },
  mutations: {
    addProblem(state: ProblemState, problem: types.ProblemInfo) {
      if (Object.prototype.hasOwnProperty.call(state.problems, problem.alias)) {
        return;
      }
      Vue.set(state.problems, problem.alias, problem);
    },
  },
};

export default new Vuex.Store<ProblemState>(storeConfig);
