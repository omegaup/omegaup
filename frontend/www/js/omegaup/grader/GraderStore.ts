// TODO: add return types to each of the getters
// TODO: move logic from components inside this store

import Vuex, { Commit, StoreOptions } from 'vuex';
import Vue from 'vue';

import * as Util from './util';
import * as templates from './GraderTemplates';
import { types } from '../api_types';

export type CaseKey = `${string}.${'err' | 'meta' | 'out'}`;
export interface GraderOutputs {
  [key: CaseKey]: string;
}

export interface GraderRequest {
  input: types.ProblemSettingsDistrib;
  language: string;
  source: string;
}

export interface GraderResults {
  compile_meta?: { [key: string]: types.RunMetadata };
  contest_score: number;
  groups?: types.RunDetailsGroup[];
  judged_by: string;
  max_score?: number;
  memory?: number;
  score: number;
  time?: number;
  verdict: string;
  wall_time?: number;

  // compile_error attribute has been added here for now
  // it might not be the correct place for it and removed later
  compile_error?: string;
}

export interface GraderSessionStorageSources {
  language: string;
  sources: { [key: string]: string };
}

export interface GraderStore {
  alias: string;
  compilerOutput: string;
  currentCase: string;
  dirty: boolean;
  languages: string[];
  logs: string;
  outputs: GraderOutputs;
  problemsetId: boolean;
  request: GraderRequest;
  results: GraderResults;
  sessionStorageSources: GraderSessionStorageSources | null;
  showSubmitButton: boolean;
  updatingSettings: boolean;

  // new attributes separate from refactored code
  zipContent: string;
  showRunButton: boolean;
  theme: Util.MonacoThemes;
}
export interface SettingsCase {
  Name: string;
  Weight: number;
}
// this type does not exist in types
export interface SettingsCasesGroup {
  Name: string;
  Cases: SettingsCase[];
  Weight: number;
}
// TODO: combine case selector group and settings cases group
export interface CaseSelectorGroup {
  explicit: boolean;
  name: string;
  cases: {
    name: string;
    item: { in: string; out: string; weight?: number };
  }[];
}

const persistToSessionStorage = Util.throttle(
  ({
    alias,
    contents,
  }: {
    alias: string;
    contents: GraderSessionStorageSources | null;
  }) => {
    if (!contents) return;
    sessionStorage.setItem(
      `ephemeral-sources-${alias}`,
      JSON.stringify(contents),
    );
  },
  1000,
);

const defaultValidatorSource = templates.defaultValidatorSource;
const defaultInteractiveIdlSource = templates.defaultInteractiveIdlSource;
const defaultInteractiveMainSource = templates.defaultInteractiveMainSource;

const sourceTemplates = { ...templates.sourceTemplates };
const originalInteractiveTemplates = {
  ...templates.originalInteractiveTemplates,
};
const interactiveTemplates = { ...originalInteractiveTemplates };

const languageExtensionMapping: Record<string, string> = {};
Object.keys(Util.supportedLanguages).forEach((key) => {
  languageExtensionMapping[key] = Util.supportedLanguages[key].extension;
});
Vue.use(Vuex);
const storeOptions: StoreOptions<GraderStore> = {
  state: {
    alias: '',
    compilerOutput: '',
    currentCase: '',
    dirty: true,
    languages: [],
    logs: '',
    outputs: {},
    problemsetId: false,
    request: {
      input: {
        cases: {},
        limits: {
          ExtraWallTime: '0s',
          MemoryLimit: 33554432,
          OutputLimit: 10240,
          OverallWallTimeLimit: '1s',
          TimeLimit: '1s',
        },
        validator: {
          name: 'token-caseless',
        },
      },
      language: '',
      source: '',
    },
    results: {
      compile_meta: {},
      contest_score: 0,
      groups: [],
      judged_by: '',
      max_score: 0,
      memory: 0,
      score: 0,
      time: 0,
      verdict: '',
      wall_time: 0,
    },
    sessionStorageSources: null,
    showSubmitButton: false,
    updatingSettings: false,
    zipContent: '',
    showRunButton: true,
    theme: Util.MonacoThemes.VSLight,
  },
  getters: {
    alias(state: GraderStore) {
      return state.alias;
    },
    showSubmitButton(state: GraderStore) {
      return state.showSubmitButton;
    },
    languages(state: GraderStore) {
      return state.languages;
    },
    sessionStorageSources(state: GraderStore) {
      return state.sessionStorageSources;
    },
    moduleName(state: GraderStore) {
      return (
        state.request.input.interactive?.module_name || state.alias || 'Main'
      );
    },
    flatCaseResults(state: GraderStore) {
      const result: { [key: string]: types.CaseResult } = {};
      if (!state.results || !state.results.groups) return result;
      for (const group of state.results.groups) {
        for (const caseData of group.cases) {
          result[caseData.name] = caseData;
        }
      }
      return result;
    },
    currentCase(state: GraderStore) {
      return state.currentCase;
    },
    inputIn(state: GraderStore) {
      return state.request.input.cases[state.currentCase]['in'];
    },
    inputOut(state: GraderStore) {
      return state.request.input.cases[state.currentCase]['out'];
    },
    outputStdout(state: GraderStore) {
      const filename: CaseKey = `${state.currentCase}.out`;
      return state.outputs[filename] || '';
    },
    outputStderr(state: GraderStore) {
      const filename: CaseKey = `${state.currentCase}.err`;
      return state.outputs[filename] || '';
    },
    settingsCases(state: GraderStore) {
      // resultMap type is not present in types
      const resultMap: {
        [key: string]: SettingsCasesGroup;
      } = {};
      for (const caseName in state.request.input.cases) {
        if (
          !Object.prototype.hasOwnProperty.call(
            state.request.input.cases,
            caseName,
          )
        )
          continue;
        const tokens = caseName.split('.', 2);
        if (!Object.prototype.hasOwnProperty.call(resultMap, tokens[0])) {
          resultMap[tokens[0]] = {
            Name: tokens[0],
            Cases: [],
            Weight: 0,
          };
        }
        resultMap[tokens[0]].Cases.push({
          Name: caseName,
          Weight: state.request.input.cases[caseName].weight || 0,
        });
        resultMap[tokens[0]].Weight +=
          state.request.input.cases[caseName].weight || 0;
      }
      const result: SettingsCasesGroup[] = [];
      for (const groupName in resultMap) {
        if (!Object.prototype.hasOwnProperty.call(resultMap, groupName))
          continue;
        resultMap[groupName].Cases.sort((a: SettingsCase, b: SettingsCase) => {
          if (a.Name < b.Name) return -1;
          if (a.Name > b.Name) return 1;
          return 0;
        });
        result.push(resultMap[groupName]);
      }
      result.sort((a, b) => {
        if (a.Name < b.Name) return -1;
        if (a.Name > b.Name) return 1;
        return 0;
      });
      return result;
    },
    'request.source'(state: GraderStore) {
      return state.request.source;
    },
    'request.language'(state: GraderStore) {
      return state.request.language;
    },
    'request.input.validator.custom_validator.language'(state: GraderStore) {
      return state.request.input.validator.custom_validator?.language || '';
    },
    'request.input.validator.custom_validator.source'(state: GraderStore) {
      return state.request.input.validator.custom_validator?.source || '';
    },
    'request.input.interactive.idl'(state: GraderStore) {
      return state.request.input.interactive?.idl || '';
    },
    'request.input.interactive.main_source'(state: GraderStore) {
      return state.request.input.interactive?.main_source || '';
    },
    'request.input.interactive.language'(state: GraderStore) {
      return state.request.input.interactive?.language || '';
    },
    Tolerance(state: GraderStore) {
      return state.request.input.validator?.tolerance || 0;
    },
    isCustomValidator(state: GraderStore) {
      return !!state.request.input.validator.custom_validator;
    },
    isInteractive(state: GraderStore) {
      return !!state.request.input.interactive;
    },
    isUpdatingSettings(state: GraderStore) {
      return state.updatingSettings;
    },
    isDirty(state: GraderStore) {
      return state.dirty;
    },
    // new getters separate from refactored code
    zipContent(state: GraderStore) {
      return state.zipContent;
    },
    compilerOutput(state: GraderStore) {
      return state.compilerOutput;
    },
    logs(state: GraderStore) {
      return state.logs;
    },
    Validator(state: GraderStore) {
      return state.request.input.validator.name;
    },
    caseSelectorGroups(state: GraderStore): CaseSelectorGroup[] {
      const flatCases = state.request.input.cases;

      const resultMap: {
        [key: string]: CaseSelectorGroup;
      } = {};

      for (const caseName in flatCases) {
        if (!flatCases[caseName]) continue;

        const tokens = caseName.split('.', 2);
        if (!resultMap[tokens[0]]) {
          resultMap[tokens[0]] = {
            explicit: tokens.length > 1,
            name: tokens[0],
            cases: [],
          };
        }

        resultMap[tokens[0]].cases.push({
          name: caseName,
          item: flatCases[caseName],
        });
      }

      const result: CaseSelectorGroup[] = [];
      for (const groupName in resultMap) {
        if (!resultMap[groupName]) continue;

        resultMap[groupName].cases.sort(
          (
            a: {
              name: string;
              item: { in: string; out: string; weight?: number };
            },
            b: {
              name: string;
              item: { in: string; out: string; weight?: number };
            },
          ) => (a.name < b.name ? -1 : a.name > b.name ? 1 : 0),
        );
        result.push(resultMap[groupName]);
      }
      result.sort((a: CaseSelectorGroup, b: CaseSelectorGroup) =>
        a.name < b.name ? -1 : a.name > b.name ? 1 : 0,
      );
      return result;
    },
    showRunButton(state: GraderStore) {
      return state.showRunButton;
    },
    request(state: GraderStore) {
      return state.request;
    },
    customValidator(state: GraderStore) {
      return state.request.input.validator.custom_validator;
    },
    inputCases(state: GraderStore) {
      return state.request.input.cases;
    },
    Interactive(state: GraderStore) {
      return state.request.input.interactive;
    },
    limits(state: GraderStore) {
      return state.request.input.limits;
    },
    theme(state: GraderStore) {
      return state.theme;
    },
  },
  mutations: {
    alias(
      state: GraderStore,
      {
        alias,
        initialLanguage,
        initialSource = '',
      }: {
        alias: string;
        initialLanguage: string;
        initialSource?: string;
      },
    ) {
      if (state.alias) {
        persistToSessionStorage(state.alias).flush?.();
      }

      state.sessionStorageSources = null;
      state.alias = alias;
      const itemString = sessionStorage.getItem(
        `ephemeral-sources-${state.alias}`,
      );

      if (itemString) {
        state.sessionStorageSources = JSON.parse(itemString);
      }
      if (!state.sessionStorageSources) {
        state.sessionStorageSources = {
          language: initialLanguage,
          sources: state.request.input.interactive
            ? { ...interactiveTemplates }
            : { ...sourceTemplates },
        };
      }
      // do not persist storage sources
      state.request.language = state.sessionStorageSources.language;
      state.request.source =
        initialSource ||
        state.sessionStorageSources.sources[
          languageExtensionMapping[state.request.language]
        ];
    },
    showSubmitButton(state: GraderStore, value: boolean) {
      state.showSubmitButton = value;
    },
    languages(state: GraderStore, languages: string[]) {
      state.languages = languages;
    },
    currentCase(state: GraderStore, value: string) {
      state.currentCase = value;
    },
    compilerOutput(state: GraderStore, value: string) {
      state.compilerOutput = value;
    },
    logs(state: GraderStore, value: string) {
      state.logs = value;
    },
    request(state: GraderStore, value: GraderRequest) {
      Vue.set(state, 'request', value);
    },
    'request.language'(state: GraderStore, language: string) {
      state.request.language = language;
      const extension = languageExtensionMapping[language];

      if (!extension) {
        state.dirty = true;
        return;
      }

      if (state.sessionStorageSources) {
        if (state.sessionStorageSources.sources[extension]) {
          state.request.source = state.sessionStorageSources.sources[extension];
        }
      } else if (state.request.input.interactive) {
        if (interactiveTemplates[extension]) {
          state.request.source = interactiveTemplates[extension];
        }
      } else {
        if (sourceTemplates[extension]) {
          state.request.source = sourceTemplates[extension];
        }
      }

      if (state.sessionStorageSources && !state.updatingSettings) {
        state.sessionStorageSources.language = language;
        persistToSessionStorage(state.alias)({
          alias: state.alias,
          contents: state.sessionStorageSources,
        });
      }

      state.dirty = true;
    },
    'request.source'(state: GraderStore, value: string) {
      state.request.source = value;
      if (state.updatingSettings || !state.sessionStorageSources) {
        state.dirty = true;
        return;
      }

      state.sessionStorageSources.sources[
        languageExtensionMapping[state.sessionStorageSources.language]
      ] = value;
      persistToSessionStorage(state.alias)({
        alias: state.alias,
        contents: state.sessionStorageSources,
      });

      state.dirty = true;
    },
    inputIn(state: GraderStore, value: string) {
      state.request.input.cases[state.currentCase]['in'] = value;
      state.dirty = true;
    },
    inputOut(state: GraderStore, value: string) {
      state.request.input.cases[state.currentCase]['out'] = value;
      state.dirty = true;
    },
    results(state: GraderStore, value: GraderResults) {
      Vue.set(state, 'results', value);
      state.dirty = true;
    },
    clearOutputs(state: GraderStore) {
      Vue.set(state, 'outputs', {});
    },
    output(state: GraderStore, payload: { name: CaseKey; contents: string }) {
      Vue.set(state.outputs, payload.name, payload.contents);
    },
    'request.input.validator.custom_validator.source'(
      state: GraderStore,
      value: string,
    ) {
      if (!state.request.input.validator.custom_validator) return;
      state.request.input.validator.custom_validator.source = value;
      state.dirty = true;
    },
    'request.input.interactive.idl'(state: GraderStore, value: string) {
      if (!state.request.input.interactive) return;
      state.request.input.interactive.idl = value;
      state.dirty = true;
    },
    'request.input.interactive.main_source'(state: GraderStore, value: string) {
      if (!state.request.input.interactive) return;
      state.request.input.interactive.main_source = value;
      state.dirty = true;
    },
    TimeLimit(state: GraderStore, value: string) {
      state.request.input.limits.TimeLimit = value;
      state.dirty = true;
    },
    OverallWallTimeLimit(state: GraderStore, value: string) {
      state.request.input.limits.OverallWallTimeLimit = value;
      state.dirty = true;
    },
    ExtraWallTime(state: GraderStore, value: string) {
      state.request.input.limits.ExtraWallTime = value;
      state.dirty = true;
    },
    MemoryLimit(state: GraderStore, value: number | string) {
      state.request.input.limits.MemoryLimit = value;
      state.dirty = true;
    },
    OutputLimit(state: GraderStore, value: number | string) {
      state.request.input.limits.OutputLimit = value;
      state.dirty = true;
    },
    Validator(state: GraderStore, value: string) {
      if (value == 'token-numeric') {
        if (
          !Object.prototype.hasOwnProperty.call(
            state.request.input.validator,
            'tolerance',
          )
        )
          Vue.set(state.request.input.validator, 'tolerance', 1e-9);
      } else {
        Vue.delete(state.request.input.validator, 'tolerance');
      }
      if (value == 'custom') {
        if (
          !Object.prototype.hasOwnProperty.call(
            state.request.input.validator,
            'custom_validator',
          )
        ) {
          Vue.set(state.request.input.validator, 'custom_validator', {
            source: defaultValidatorSource,
            language: 'py3',
          });
        }
      } else {
        Vue.delete(state.request.input.validator, 'custom_validator');
      }

      Vue.set(state.request.input.validator, 'name', value);
      state.dirty = true;
    },
    Tolerance(state: GraderStore, value: number) {
      state.request.input.validator.tolerance = value;
      state.dirty = true;
    },
    'request.input.validator.custom_validator.language'(
      state: GraderStore,
      value: string,
    ) {
      if (!state.request.input.validator.custom_validator) return;
      Vue.set(
        state.request.input.validator.custom_validator,
        'language',
        value,
      );
      state.dirty = true;
    },
    Interactive(
      state: GraderStore,
      value: Partial<types.InteractiveSettingsDistrib> | undefined,
    ) {
      const isInteractive = !!value;
      if (!isInteractive) {
        if (!state.request.input.interactive) {
          return;
        }
        Vue.delete(state.request.input, 'interactive');
        state.dirty = true;
        return;
      }

      // initialize interactive if its not present in state
      if (
        !Object.prototype.hasOwnProperty.call(
          state.request.input,
          'interactive',
        )
      ) {
        Vue.set(state.request.input, 'interactive', {});
      }

      // update interactive problem data
      const {
        idl = state.request.input.interactive?.idl ||
          defaultInteractiveIdlSource,
        module_name = state.request.input.interactive?.module_name || 'sumas',
        language = state.request.input.interactive?.language || 'cpp17-gcc',
        main_source = state.request.input.interactive?.main_source ||
          defaultInteractiveMainSource,
        templates = state.request.input.interactive?.templates ||
          originalInteractiveTemplates,
      } = value;

      store.commit('request.input.interactive.idl', idl);
      store.commit('request.input.interactive.language', language);
      store.commit('moduleName', module_name);
      store.commit('request.input.interactive.main_source', main_source);
      // if its the same template from before, no need to update
      if (templates == state.request.input.interactive?.templates) {
        state.dirty = true;
        return;
      }

      for (const extension in originalInteractiveTemplates) {
        if (templates[extension]) {
          interactiveTemplates[extension] = templates[extension];
        } else {
          interactiveTemplates[extension] =
            originalInteractiveTemplates[extension];
        }
      }
      store.commit('request.language', state.request.language);
      state.dirty = true;
    },
    'request.input.interactive.language'(state: GraderStore, value: string) {
      if (value == 'cpp') value = 'cpp17-gcc';
      if (!state.request.input.interactive) return;

      Vue.set(state.request.input.interactive, 'language', value);
      state.dirty = true;
    },
    moduleName(state: GraderStore, value: string) {
      if (!state.request.input.interactive) return;

      // this statement is not reactive
      // state.request.input.interactive.module_name = value;

      // this one is reactive
      Vue.set(state.request.input.interactive, 'module_name', value);
      state.dirty = true;
    },
    updatingSettings(state: GraderStore, value: boolean) {
      state.updatingSettings = value;
    },
    createCase(
      state: GraderStore,
      caseData: { name: string; in?: string; out?: string; weight?: number },
    ) {
      // if case doesn't already exist create it?
      // no! always create a case
      // 2 cases can be of same name and different data

      Vue.set(state.request.input.cases, caseData.name, {
        in: caseData.in || '',
        out: caseData.out || '',
        weight: caseData.weight || 1,
      });
      // if we call this function, we must set current case
      // or it could cause errors
      store.commit('currentCase', caseData.name);
      state.dirty = true;
    },
    removeCase(state: GraderStore, name: string) {
      if (
        !Object.prototype.hasOwnProperty.call(state.request.input.cases, name)
      )
        return;

      const keys = Object.keys(store.state.request.input.cases);

      // do not delete if there is only one test case
      if (keys.length === 1) {
        return;
      }

      // switch to a random case
      const caseName = keys[0] === name ? keys[1] : keys[0];
      store.commit('currentCase', caseName);

      Vue.delete(state.request.input.cases, name);
      state.dirty = true;
    },
    limits(_state: GraderStore, limits: types.LimitsSettings) {
      store.commit(
        'MemoryLimit',
        // is memory implicitly stored in units of kilobytes?
        // Util.parseDuration(limits.MemoryLimit) * 1024,
        Util.parseDuration(limits.MemoryLimit),
      );

      store.commit('OutputLimit', limits.OutputLimit);
      store.commit('TimeLimit', limits.TimeLimit);
      store.commit('OverallWallTimeLimit', limits.OverallWallTimeLimit);
      store.commit('ExtraWallTime', limits.ExtraWallTime);
    },
    zipContent(state: GraderStore, value: string) {
      state.zipContent = value;
    },
    showRunButton(state: GraderStore, value: boolean) {
      state.showRunButton = value;
    },
    isDirty(state: GraderStore, value: boolean) {
      state.dirty = value;
    },
    theme(state: GraderStore, value: Util.MonacoThemes) {
      state.theme = value;
    },
  },
  actions: {
    zipContent({ commit }: { commit: Commit }, value: string) {
      commit('zipContent', value);
    },
    compilerOutput({ commit }: { commit: Commit }, value: string) {
      commit('compilerOutput', value);
    },
    logs({ commit }: { commit: Commit }, value: string) {
      commit('logs', value);
    },
    inputIn({ commit }: { commit: Commit }, value: string) {
      commit('inputIn', value);
    },
    inputOut({ commit }: { commit: Commit }, value: string) {
      commit('inputOut', value);
    },
    'request.source'({ commit }: { commit: Commit }, value: string) {
      commit('request.source', value);
    },
    limits({ commit }: { commit: Commit }, limits: types.LimitsSettings) {
      commit('limits', limits);
    },
    Interactive(
      { commit }: { commit: Commit },
      interactiveSettings:
        | Partial<types.InteractiveSettingsDistrib>
        | undefined,
    ) {
      commit('Interactive', interactiveSettings);
    },
    'request.input.validator.custom_validator.language'(
      { commit }: { commit: Commit },
      language: string,
    ) {
      commit('request.input.validator.custom_validator.language', language);
    },
    'request.input.interactive.language'(
      { commit }: { commit: Commit },
      language: string,
    ) {
      commit('request.input.interactive.language', language);
    },
    moduleName({ commit }: { commit: Commit }, moduleName: string) {
      commit('moduleName', moduleName);
    },
    Tolerance({ commit }: { commit: Commit }, value: number) {
      commit('Tolerance', value);
    },
    Validator({ commit }: { commit: Commit }, value: string) {
      commit('Validator', value);
    },
    currentCase({ commit }: { commit: Commit }, value: string) {
      commit('currentCase', value);
    },
    createCase(
      { commit }: { commit: Commit },
      caseData: { name: string; in?: string; out?: string; weight?: number },
    ) {
      commit('createCase', caseData);
    },
    removeCase({ commit }: { commit: Commit }, name: string) {
      commit('removeCase', name);
    },
    'request.language'({ commit }: { commit: Commit }, value: string) {
      commit('request.language', value);
    },
    results({ commit }: { commit: Commit }, value: GraderResults) {
      commit('results', value);
    },
    output(
      { commit }: { commit: Commit },
      payload: { name: CaseKey; contents: string },
    ) {
      commit('output', payload);
    },
    clearOutputs({ commit }: { commit: Commit }) {
      commit('clearOutputs');
    },
    'request.input.validator.custom_validator.source'(
      { commit }: { commit: Commit },
      value: string,
    ) {
      commit('request.input.validator.custom_validator.source', value);
    },
    isDirty({ commit }: { commit: Commit }, value: boolean) {
      commit('isDirty', value);
    },
    Toleration({ commit }: { commit: Commit }, value: number) {
      commit('Toleration', value);
    },
    theme({ commit }: { commit: Commit }, value: string) {
      commit('theme', value);
    },
    reset({ commit }: { commit: Commit }) {
      commit(
        'languages',
        Object.values(Util.supportedLanguages).map(
          (languageInfo) => languageInfo.language,
        ),
      );
      commit('Interactive', undefined);
      commit('request.language', 'cpp17-gcc');
      commit('request.source', sourceTemplates.cpp);

      commit('TimeLimit', '1s');
      commit('MemoryLimit', 67108864);
      commit('OverallWallTimeLimit', '5s');
      commit('ExtraWallTime', '0s');
      commit('OutputLimit', 10240);

      commit('Validator', 'token-caseless');
      commit('Tolerance', 0);

      commit('createCase', {
        name: 'sample',
        in: '1 2\n',
        out: '3\n',
        weight: 1,
      });
      commit('createCase', {
        name: 'long',
        in: '123456789012345678 123456789012345678\n',
        out: '246913578024691356\n',
        weight: 1,
      });

      commit('clearOutputs');
      commit('logs', '');
      commit('compilerOutput', '');

      commit('updatingSettings', false);
      store.state.dirty = true;
    },
    initProblem(
      { commit }: { commit: Commit },
      {
        initialLanguage,
        initialSource = '',
        initialTheme,
        languages,
        problem,
        showRunButton,
        showSubmitButton,
      }: {
        initialLanguage: string;
        initialSource: string;
        initialTheme: Util.MonacoThemes;
        languages: string[];
        problem: types.ProblemInfo;
        showRunButton: boolean;
        showSubmitButton: boolean;
      },
    ) {
      const { alias, settings } = problem;

      if (!alias || !settings) {
        return;
      }

      commit('languages', languages);
      commit('Interactive', settings.interactive);
      commit('alias', {
        alias,
        initialLanguage,
        initialSource,
      });

      commit('showSubmitButton', showSubmitButton);
      commit('showRunButton', showRunButton);
      commit('theme', initialTheme);
      commit('limits', settings.limits);
      commit('Validator', settings.validator.name);
      commit('Tolerance', settings.validator.tolerance);

      // when there are no problem statement I/O
      // settings.cases become an empty object
      if (!Object.keys(settings.cases).length) {
        commit('createCase', {
          name: 'sample',
          in: '1 2\n',
          out: '3\n',
          weight: 1,
        });
        for (const caseName of Object.keys(store.state.request.input.cases)) {
          if (caseName == 'sample') continue;
          commit('removeCase', caseName);
        }
        return;
      }

      // create cases for current problem
      for (const caseName in settings.cases) {
        if (!settings.cases[caseName]) continue;
        const caseData = settings.cases[caseName];
        commit('createCase', {
          name: caseName,
          weight: caseData.weight,
          in: caseData['in'],
          out: caseData['out'],
        });
      }
      // delete cases that are not in settings cases
      for (const caseName of Object.keys(store.state.request.input.cases)) {
        if (settings.cases[caseName]) continue;
        commit('removeCase', caseName);
      }
    },
  },
  strict: true,
};
const store = new Vuex.Store<GraderStore>(storeOptions);
export default store;
