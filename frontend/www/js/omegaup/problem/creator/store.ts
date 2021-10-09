import Vue from 'vue';
import Vuex, { StoreOptions } from 'vuex';
import { types } from './types';
import { casesStore } from './modules/cases';

Vue.use(Vuex);
Vue.config.devtools = true;

const store: StoreOptions<types.RootState> = {
  state: {
    problemName: 'Nuevo Problema',
  },
  modules: {
    casesStore,
  },
};

export default new Vuex.Store<types.RootState>(store);
