import Vue from 'vue';
import Vuex from 'vuex';
import { types } from '../api_types';

Vue.use(Vuex);

export interface ClarifiactionState {
  // The list of clarifications
  clarifications: types.Clarification[];

  // The mapping of clarificationIds to indices on the clarifications array
  // useful to keep the correct order
  index: Record<number, number>;
}

const clarificationStore = new Vuex.Store<ClarifiactionState>({
  state: {
    clarifications: [],
    index: {},
  },
  mutations: {
    addClarification(state, clarification: types.Clarification) {
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
    clear(state) {
      state.clarifications.splice(0);
      state.index = {};
    },
  },
});

export default clarificationStore;
