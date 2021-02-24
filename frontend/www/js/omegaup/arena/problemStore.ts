import Vue from 'vue';
import Vuex from 'vuex';
import { types } from '../api_types';

Vue.use(Vuex);

export interface ProblemState {
  // The mapping of problem alias to indices on the problems array
  problems: Record<string, types.ProblemInfo>;
}

const problemsStore = new Vuex.Store<ProblemState>({
  state: {
    problems: {},
  },
  mutations: {
    addProblem(state, problem: types.ProblemInfo) {
      if (Object.prototype.hasOwnProperty.call(state.problems, problem.alias)) {
        return;
      }
      Vue.set(state.problems, problem.alias, problem);
    },
  },
});

export default problemsStore;
