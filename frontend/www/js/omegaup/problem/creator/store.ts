import Vue from 'vue';
import Vuex, { Module } from 'vuex';
import { StoreState, RootState } from './types';
import { casesStore } from './modules/cases';
import T from '../../lang';

Vue.use(Vuex);
Vue.config.devtools = true;

const state: StoreState = {
  problemName: T.problemCreatorNamePlaceholder,
} as StoreState;

const store: Module<StoreState, RootState> = {
  state,
  modules: {
    casesStore,
  },
};

export default new Vuex.Store(store);
