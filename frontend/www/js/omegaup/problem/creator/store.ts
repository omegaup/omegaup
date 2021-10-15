import Vue from 'vue';
import Vuex, { Module } from 'vuex';
import { types } from './types';
import { casesStore } from './modules/cases';
import T from '../../lang';

Vue.use(Vuex);
Vue.config.devtools = true;

const state: types.StoreState = {
  problemName: T.problemCreatorNamePlaceholder,
} as types.StoreState;

const store: Module<types.StoreState, types.RootState> = {
  state,
  modules: {
    casesStore,
  },
};

export default new Vuex.Store(store);
