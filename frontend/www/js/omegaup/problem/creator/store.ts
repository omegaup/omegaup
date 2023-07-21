import Vue from 'vue';
import Vuex from 'vuex';
import { StoreState } from './types';
import { casesStore } from './modules/cases';
import T from '../../lang';

Vue.use(Vuex);

const state: StoreState = {
  problemName: T.problemCreatorNewProblem,
} as StoreState;

export default new Vuex.Store({
  state,
  modules: {
    casesStore,
  },
});
