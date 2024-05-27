import Vue from 'vue';
import Vuex from 'vuex';
import { StoreState } from './types';
import { casesStore } from './modules/cases';
import T from '../../lang';

Vue.use(Vuex);

const state: StoreState = {
  problemName: T.problemCreatorNewProblem,
  problemMarkdown: T.problemCreatorEmpty,
} as StoreState;

export default new Vuex.Store({
  state,
  modules: {
    casesStore,
  },
  mutations: {
    updateMarkdown(state: StoreState, newMarkdown: string) {
      state.problemMarkdown = newMarkdown;
    },
    updateName(state: StoreState, newName: string) {
      state.problemName = newName;
    },
  },
});
