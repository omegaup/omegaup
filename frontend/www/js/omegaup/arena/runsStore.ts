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
  problem?: string;
  status?: string;
}

export interface RunsState {
  // The list of runs.
  runs: types.RunWithDetails[];

  // The mapping of run GUIDs to indices on the runs array.
  index: Record<string, number>;

  filters?: RunFilters;

  totalRuns: number;
}

export const runsStoreConfig = {
  state: {
    runs: [],
    index: {},
    filters: {},
    totalRuns: 0,
  },
  mutations: {
    addRun(state: RunsState, run: types.RunWithDetails) {
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
    addRunDetails(state: RunsState, {
      runGUID,
      runDetails,
    }: {
      runGUID: string;
      runDetails: types.RunDetails;
    }) {
      if (Object.prototype.hasOwnProperty.call(state.index, runGUID)) {
        const run = state.runs[state.index[runGUID]];
        run.details = runDetails;
        Vue.set(
          state.runs,
          state.index[run.guid],
          Object.assign({}, state.runs[state.index[run.guid]], run),
        );
      }
    },
    setTotalRuns(state: RunsState, totalRuns: number) {
      Vue.set(state, 'totalRuns', totalRuns);
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
      filter:
        | 'verdict'
        | 'language'
        | 'username'
        | 'status'
        | 'offset'
        | 'problem',
    ) {
      if (!state.filters) {
        return;
      }
      delete state.filters[filter];
    },
  },
};

export const myRunsStore = new Vuex.Store<RunsState>({
  state: {
    runs: [],
    index: {},
    totalRuns: 0,
  },
  mutations: {
    addRun(state, run: types.RunWithDetails) {
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
    addRunDetails(state: RunsState, {
      runGUID,
      runDetails,
    }: {
      runGUID: string;
      runDetails: types.RunDetails;
    }) {
      if (Object.prototype.hasOwnProperty.call(state.index, runGUID)) {
        const run = state.runs[state.index[runGUID]];
        run.details = runDetails;
        Vue.set(
          state.runs,
          state.index[run.guid],
          Object.assign({}, state.runs[state.index[run.guid]], run),
        );
      }
    },
    clear(state) {
      state.runs.splice(0);
      state.index = {};
    },
  },
});

export const runsStore = new Vuex.Store<RunsState>(runsStoreConfig);
