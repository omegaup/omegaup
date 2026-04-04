import Vue from 'vue';
import Vuex from 'vuex';
import { types } from '../api_types';

Vue.use(Vuex);

export interface ClarificationState {
  // The list of clarifications
  clarifications: types.Clarification[];

  // The mapping of clarificationIds to indices on the clarifications array
  // useful to keep the correct order
  index: Record<number, number>;

  selectedClarificationId: null | number;
}

export const clarificationStoreConfig = {
  state: {
    clarifications: [],
    index: {},
    selectedClarificationId: null,
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
    selectClarificationId(
      state: ClarificationState,
      clarificationId: null | number,
    ) {
      state.selectedClarificationId = clarificationId;
    },
    clear(state: ClarificationState) {
      state.clarifications.splice(0);
      state.index = {};
      state.selectedClarificationId = null;
    },
  },
};

export default new Vuex.Store<ClarificationState>(clarificationStoreConfig);
