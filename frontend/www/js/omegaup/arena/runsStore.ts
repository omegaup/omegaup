import { createStore } from 'vuex';
import { types } from '../api_types';

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
  // The list of runs.
  runs: types.Run[];

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
    addRun(state: RunsState, run: types.Run) {
      if (Object.prototype.hasOwnProperty.call(state.index, run.guid)) {
        state.runs[state.index[run.guid]] = Object.assign(
          {},
          state.runs[state.index[run.guid]],
          run,
        );
        return;
      }
      state.index = { ...state.index, [run.guid]: state.runs.length };
      state.runs.push(run);
    },
    setTotalRuns(state: RunsState, totalRuns: number) {
      state.totalRuns = totalRuns;
    },
    clear(state: RunsState) {
      state.runs.splice(0);
      state.index = {};
    },
    appendRuns(state: RunsState, runs: types.Run[]) {
      for (const run of runs) {
        if (Object.prototype.hasOwnProperty.call(state.index, run.guid)) {
          state.runs[state.index[run.guid]] = Object.assign(
            {},
            state.runs[state.index[run.guid]],
            run,
          );
        } else {
          state.index = { ...state.index, [run.guid]: state.runs.length };
          state.runs.push(run);
        }
      }
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
        | 'problem'
        | 'execution'
        | 'output',
    ) {
      if (!state.filters) {
        return;
      }
      delete state.filters[filter];
    },
  },
};

export const myRunsStore = createStore<RunsState>({
  state: {
    runs: [],
    index: {},
    totalRuns: 0,
  },
  mutations: {
    addRun(state, run: types.Run) {
      if (Object.prototype.hasOwnProperty.call(state.index, run.guid)) {
        state.runs[state.index[run.guid]] = Object.assign(
          {},
          state.runs[state.index[run.guid]],
          run,
        );
        return;
      }
      state.index = { ...state.index, [run.guid]: state.runs.length };
      state.runs.push(run);
    },
    clear(state) {
      state.runs.splice(0);
      state.index = {};
    },
  },
});

export const runsStore = createStore<RunsState>(runsStoreConfig);
