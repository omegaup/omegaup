import Vue from 'vue';
import { createStore, Store } from 'vuex';

export interface SocketState {
  socketStatus: SocketStatus;
}

enum SocketStatus {
  Waiting = '↻',
  Failed = '✗',
  Connected = '•',
}

export const socketStoreConfig = createStore({
  state() {
    return {
      socketStatus: SocketStatus.Waiting,
    };
  },
  mutations: {
    updateSocketStatus(state: SocketState, socketStatus: SocketStatus) {
      Vue.set(state, 'socketStatus', socketStatus);
    },
  },
});

export default new Store<SocketState>(socketStoreConfig);
