import { createStore } from 'vuex';

export interface MainStoreState {
  username: string | null;
}

export const mainStoreConfig = {
  state: {
    username: null,
  },
  mutations: {
    updateUsername(state: MainStoreState, username: string | null) {
      state.username = username;
    },
  },
};

export default createStore<MainStoreState>(mainStoreConfig);
