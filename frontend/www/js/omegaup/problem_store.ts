import Vue from 'vue';
import Vuex, { StoreOptions } from 'vuex';

Vue.use(Vuex);

interface ProblemState {
  showOverlay: boolean;
}

export const problemStore = new Vuex.Store<ProblemState>({
  state: { showOverlay: false },
  mutations: {
    toggleOverlay(state, showOverlay: boolean) {
      state.showOverlay = !showOverlay;
    },
  },
});
