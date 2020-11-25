import Vue from 'vue';
import Vuex from 'vuex';
import { types } from '../api_types';

Vue.use(Vuex);

export interface RunsState {
  // The list of runs.
  runs: types.Run[];

  // The mapping of run GUIDs to indices on the runs array.
  index: Record<string, number>;
}

export const runsStore = new Vuex.Store<RunsState>({
  state: {
    runs: [],
    index: {},
  },
  mutations: {
    addRun(state, run: types.Run) {
      if (Object.prototype.hasOwnProperty.call(state.index, run.guid)) {
        Vue.set(
          state.runs,
          state.index[run.guid],
          Object.assign({}, state.runs[state.index[run.guid]], run),
        );
        return;
      }
      Vue.set(state.index, run.guid, state.runs.length);
      state.runs.push(run);
    },
    clear(state) {
      state.runs.splice(0);
      state.index = {};
    },
  },
});

export const myRunsStore = new Vuex.Store<RunsState>({
  state: {
    runs: [],
    index: {},
  },
  mutations: {
    addRun(state, run: types.Run) {
      if (Object.prototype.hasOwnProperty.call(state.index, run.guid)) {
        Vue.set(
          state.runs,
          state.index[run.guid],
          Object.assign({}, state.runs[state.index[run.guid]], run),
        );
        return;
      }
      Vue.set(state.index, run.guid, state.runs.length);
      state.runs.push(run);
    },
    clear(state) {
      state.runs.splice(0);
      state.index = {};
    },
  },
});
