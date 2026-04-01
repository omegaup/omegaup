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
  execution?: string;
  output?: string;
}

export interface RunsState {
  runs: types.Run[];
  index: Record<string, number>;
  filters: RunFilters; // Non-optional now
  totalRuns: number;
}

export const runsStoreConfig = {
  state: {
    runs: [] as types.Run[],
    index: {} as Record<string, number>,
    filters: {} as RunFilters, // always an object
    totalRuns: 0,
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
    setTotalRuns(state: RunsState, totalRuns: number) {
      Vue.set(state, 'totalRuns', totalRuns);
    },
    clear(state: RunsState) {
      state.runs.splice(0);
      state.index = {};
      state.filters = {}; // reset filters
    },
    appendRuns(state: RunsState, runs: types.Run[]) {
      for (const run of runs) {
        if (Object.prototype.hasOwnProperty.call(state.index, run.guid)) {
          Vue.set(
            state.runs,
            state.index[run.guid],
            Object.assign({}, state.runs[state.index[run.guid]], run),
          );
        } else {
          Vue.set(state.index, run.guid, state.runs.length);
          state.runs.push(run);
        }
      }
    },
    applyFilter(state: RunsState, filter: RunFilters) {
      state.filters = { ...state.filters, ...filter };
    },
    removeFilter(
      state: RunsState,
      filter:
        | 'verdict'
        | 'language'
        | 'username'
        | 'status'
        | 'offset'
        | 'problem'
        | 'execution'
        | 'output',
    ) {
      delete state.filters[filter];
    },
  },
};

export const myRunsStore = new Vuex.Store<RunsState>({
  state: {
    runs: [],
    index: {},
    filters: {}, // initialize filters here too
    totalRuns: 0,
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
      state.filters = {}; // reset filters
    },
  },
});

export const runsStore = new Vuex.Store<RunsState>(runsStoreConfig);