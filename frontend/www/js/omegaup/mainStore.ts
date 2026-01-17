import Vue from 'vue';
import Vuex from 'vuex';

Vue.use(Vuex);

export interface MainStoreState {
  username: string | null;
}

export const mainStoreConfig = {
  state: {
    username: null,
  },
  mutations: {
    updateUsername(state: MainStoreState, username: string | null) {
      Vue.set(state, 'username', username);
    },
  },
};

export default new Vuex.Store<MainStoreState>(mainStoreConfig);
