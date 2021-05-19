import Vue from 'vue';
import { createStore, Store } from 'vuex';
import { types } from '../api_types';

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

export const runsStoreConfig = createStore({
  state() {
    return {
      runs: [],
      index: {},
      filters: {},
    };
  },
  mutations: {
    addRun(state: RunsState, run: types.Run) {
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
    clear(state: RunsState) {
      state.runs.splice(0);
      state.index = {};
    },
    applyFilter(state: RunsState, filter: RunFilters) {
      state.filters = Object.assign(state.filters, filter);
    },
    removeFilter(
      state: RunsState,
      filter: 'verdict' | 'language' | 'username' | 'status',
    ) {
      if (!state.filters) {
        return;
      }
      delete state.filters[filter];
    },
  },
});

export const myRunsStore = new Store<RunsState>({
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

export const runsStore = new Store<RunsState>(runsStoreConfig);
