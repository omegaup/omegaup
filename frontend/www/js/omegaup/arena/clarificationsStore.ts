import Vue from 'vue';
import { createStore, Store } from 'vuex';
import { types } from '../api_types';

export interface ClarificationState {
  // The list of clarifications
  clarifications: types.Clarification[];

  // The mapping of clarificationIds to indices on the clarifications array
  // useful to keep the correct order
  index: Record<number, number>;
}

export const clarificationStoreConfig = createStore({
  state() {
    return {
      clarifications: [],
      index: {},
    };
  },
  mutations: {
    addClarification(
      state: ClarificationState,
      clarification: types.Clarification,
    ) {
      if (
        Object.prototype.hasOwnProperty.call(
          state.index,
          clarification.clarification_id,
        )
      ) {
        Vue.set(
          state.clarifications,
          state.index[clarification.clarification_id],
          Object.assign(
            {},
            state.clarifications[state.index[clarification.clarification_id]],
            clarification,
          ),
        );
        return;
      }
      Vue.set(
        state.index,
        clarification.clarification_id,
        state.clarifications.length,
      );
      state.clarifications.push(clarification);
    },
    clear(state: ClarificationState) {
      state.clarifications.splice(0);
      state.index = {};
    },
  },
});

export default new Store<ClarificationState>(clarificationStoreConfig);
