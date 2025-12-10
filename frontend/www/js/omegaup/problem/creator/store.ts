import Vue from 'vue';
import Vuex from 'vuex';
import { StoreState } from './types';
import { casesStore } from './modules/cases';
import T from '../../lang';

Vue.use(Vuex);

const state: StoreState = {
  problemName: T.problemCreatorNewProblem,
  problemMarkdown: T.problemCreatorEmpty,
  problemCodeContent: T.problemCreatorEmpty,
  problemCodeExtension: T.problemCreatorEmpty,
  problemSolutionMarkdown: T.problemCreatorEmpty,
} as StoreState;

export default new Vuex.Store({
  state,
  modules: {
    casesStore,
  },
  mutations: {
    updateSolutionMarkdown(state: StoreState, newSolutionMarkdown: string) {
      state.problemSolutionMarkdown = newSolutionMarkdown;
    },
    updateMarkdown(state: StoreState, newMarkdown: string) {
      state.problemMarkdown = newMarkdown;
    },
    updateName(state: StoreState, newName: string) {
      state.problemName = newName;
    },
    updateCodeContent(state: StoreState, newContent: string) {
      state.problemCodeContent = newContent;
    },
    updateCodeExtension(state: StoreState, newExtension: string) {
      state.problemCodeExtension = newExtension;
    },
    resetStore(state: StoreState) {
      state.problemName = T.problemCreatorNewProblem;
      state.problemMarkdown = T.problemCreatorEmpty;
      state.problemCodeContent = T.problemCreatorEmpty;
      state.problemCodeExtension = T.problemCreatorEmpty;
      state.problemSolutionMarkdown = T.problemCreatorEmpty;
    },
  },
});
