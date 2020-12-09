import Vue from 'vue';
import Vuex from 'vuex';

Vue.use(Vuex);

interface ProblemState {
  showOverlay: boolean;
}

const problemStore = new Vuex.Store<ProblemState>({
  state: { showOverlay: false },
  mutations: {
    toggleOverlay(state, showOverlay: boolean) {
      state.showOverlay = !showOverlay;
    },
  },
});

export default problemStore;
