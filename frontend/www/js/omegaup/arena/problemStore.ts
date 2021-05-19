import Vue from 'vue';
import { createStore, Store } from 'vuex';
import { types } from '../api_types';

export interface ProblemState {
  // The mapping of problem alias to indices on the problems array
  problems: Record<string, types.ProblemInfo>;
}

export const storeConfig = createStore({
  state() {
    return {
      problems: {},
    };
  },
  mutations: {
    addProblem(state: ProblemState, problem: types.ProblemInfo) {
      if (Object.prototype.hasOwnProperty.call(state.problems, problem.alias)) {
        return;
      }
      Vue.set(state.problems, problem.alias, problem);
    },
  },
});

export default new Store<ProblemState>(storeConfig);
