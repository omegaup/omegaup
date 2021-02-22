import Vue from 'vue';
import Vuex from 'vuex';
import { types } from '../api_types';

Vue.use(Vuex);

export interface ProblemState {
  // The maping of problem alias to indices on the problems array
  problems: types.ProblemInfo[];

  index: Record<string, number>;
}

const problemsStore = new Vuex.Store<ProblemState>({
  state: {
    problems: [],
    index: {},
  },
  mutations: {
    addProblem(state, problem: types.ProblemInfo) {
      if (Object.prototype.hasOwnProperty.call(state.index, problem.alias)) {
        Vue.set(
          state.problems,
          state.index[problem.alias],
          Object.assign(
            {},
            state.problems[state.index[problem.alias]],
            problem,
          ),
        );
        return;
      }
      Vue.set(state.index, problem.alias, state.problems.length);
      state.problems.push(problem);
    },
    // Just in case be necessary delete the list of problems
    clear(state) {
      state.problems.splice(0);
      state.index = {};
    },
  },
});

export default problemsStore;
