import Vue from 'vue';
import Vuex from 'vuex';
import { types } from '../api_types';

Vue.use(Vuex);

export interface RunFilters {
  offset?: number;
  rowcount?: number;
  verdict?: string;
  language?: string;
  username?: string;
  status?: string;
}

export interface RunsState {
  // The list of runs.
  runs: types.Run[];

  // The mapping of run GUIDs to indices on the runs array.
  index: Record<string, number>;

  filters?: RunFilters;
}

export const runsStore = new Vuex.Store<RunsState>({
  state: {
    runs: [],
    index: {},
    filters: {},
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
    applyFilter(state, filter: RunFilters) {
      const filterType: string[] = Object.keys(filter);
      switch (filterType[0]) {
        case 'verdict':
          if (!filter.verdict) {
            delete state.filters?.verdict;
            return;
          }
          break;
        case 'language':
          if (!filter.language) {
            delete state.filters?.language;
            return;
          }
          break;
        case 'username':
          if (!filter.username) {
            delete state.filters?.username;
            return;
          }
          break;
        case 'status':
          if (!filter.status) {
            delete state.filters?.status;
            return;
          }
      }
      state.filters = Object.assign(state.filters, filter);
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
