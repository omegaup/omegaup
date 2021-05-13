import Vue from 'vue';
import Vuex from 'vuex';
import { types } from '../api_types';

Vue.use(Vuex);

export interface ClarificationState {
  // The list of clarifications
  clarifications: types.Clarification[];

  // The mapping of clarificationIds to indices on the clarifications array
  // useful to keep the correct order
  index: Record<number, { order: number; selected: boolean }>;
}

export const clarificationStoreConfig = {
  state: {
    clarifications: [],
    index: {},
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
          state.index[clarification.clarification_id].order,
          Object.assign(
            {},
            state.clarifications[
              state.index[clarification.clarification_id].order
            ],
            clarification,
          ),
        );
        return;
      }
      Vue.set(state.index, clarification.clarification_id, {
        order: state.clarifications.length,
        selected: false,
      });
      state.clarifications.push(clarification);
    },
    selectClarification(state: ClarificationState, clarificationId: number) {
      if (!Object.prototype.hasOwnProperty.call(state.index, clarificationId)) {
        return;
      }
      Vue.set(state.index, clarificationId, {
        order: state.index[clarificationId].order,
        selected: true,
      });
    },
    clear(state: ClarificationState) {
      state.clarifications.splice(0);
      state.index = {};
    },
  },
};

export default new Vuex.Store<ClarificationState>(clarificationStoreConfig);
