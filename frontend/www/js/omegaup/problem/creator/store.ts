import Vue from 'vue';
import Vuex from 'vuex';
import { StoreState } from './types';
import { casesStore } from './modules/cases';
import T from '../../lang';
import * as localStorageHelper from '../../localStorage';

Vue.use(Vuex);

export const STORAGE_KEY = 'omegaup:problem-creator:draft';
export const STORAGE_TIMESTAMP_KEY = 'omegaup:problem-creator:timestamp';

function loadDraft(): Partial<StoreState> | null {
  const storedData = localStorageHelper.safeGetItem(STORAGE_KEY);
  const storedTimestamp = localStorageHelper.safeGetItem(STORAGE_TIMESTAMP_KEY);

  if (!storedData) return null;

  if (localStorageHelper.isDraftExpired(storedTimestamp)) {
    console.warn('Draft expired');
    localStorageHelper.clearDraft(STORAGE_KEY, STORAGE_TIMESTAMP_KEY);
    return null;
  }

  try {
    const parsed = JSON.parse(storedData);

    if (typeof parsed !== 'object' || parsed === null) {
      console.warn('Invalid draft data structure');
      return null;
    }

    console.log('Draft loaded successfully');
    return parsed;
  } catch (e) {
    console.warn('Failed to parse draft:', e);
    return null;
  }
}

function saveDraft(state: StoreState): void {
  const draftData = {
    problemName: state.problemName,
    problemMarkdown: state.problemMarkdown,
    problemCodeContent: state.problemCodeContent,
    problemCodeExtension: state.problemCodeExtension,
    problemSolutionMarkdown: state.problemSolutionMarkdown,
    casesStore: state.casesStore,
  };

  localStorageHelper.saveDraftWithTimestamp(
    STORAGE_KEY,
    STORAGE_TIMESTAMP_KEY,
    JSON.stringify(draftData),
    false,
  );
}

const savedDraft = loadDraft();

const defaultState: StoreState = {
  problemName: T.problemCreatorNewProblem,
  problemMarkdown: T.problemCreatorEmpty,
  problemCodeContent: T.problemCreatorEmpty,
  problemCodeExtension: T.problemCreatorEmpty,
  problemSolutionMarkdown: T.problemCreatorEmpty,
} as StoreState;

const state: StoreState = savedDraft
  ? { ...defaultState, ...savedDraft }
  : defaultState;

const store = new Vuex.Store({
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
    persistDraft(state: StoreState) {
      saveDraft(state);
    },
    resetStore(state: StoreState) {
      state.problemName = T.problemCreatorNewProblem;
      state.problemMarkdown = T.problemCreatorEmpty;
      state.problemCodeContent = T.problemCreatorEmpty;
      state.problemCodeExtension = T.problemCreatorEmpty;
      state.problemSolutionMarkdown = T.problemCreatorEmpty;

      localStorageHelper.clearDraft(STORAGE_KEY, STORAGE_TIMESTAMP_KEY);
      // Also reset the cases module state
      (store as Vuex.Store<StoreState>).commit('casesStore/resetStore');
    },
  },
});

if (savedDraft && (savedDraft as any).casesStore) {
  (store as Vuex.Store<StoreState>).commit(
    'casesStore/replaceState',
    (savedDraft as any).casesStore,
  );
}

export default store;
