import Vuex from 'vuex';
import Vue from 'vue';
import * as Util from './util';
import * as templates from './GraderTemplates';

// persist function only used in the store when some data get updated
const languageSelectElement = document.getElementById('language');
const persistToSessionStorage = Util.throttle(({ alias, contents }) => {
  sessionStorage.setItem(
    `ephemeral-sources-${alias}`,
    JSON.stringify(contents),
  );
}, 1000);

// literal strings
const defaultValidatorSource = templates.defaultValidatorSource;
const defaultInteractiveIdlSource = templates.defaultInteractiveIdlSource;
const defaultInteractiveMainSource = templates.defaultInteractiveMainSource;
// objects
const sourceTemplates = { ...templates.sourceTemplates };
const originalInteractiveTemplates = {
  ...templates.originalInteractiveTemplates,
};
const interactiveTemplates = { ...originalInteractiveTemplates };
const languageExtensionMapping = Object.fromEntries(
  Object.entries(Util.supportedLanguages).map(([key, value]) => [
    key,
    value.extension,
  ]),
);

// simple copy paste of the vuex store here
Vue.use(Vuex);
let store = new Vuex.Store({
  state: {
    alias: null,
    showSubmitButton: false,
    languages: [],
    sessionStorageSources: null,
    request: {
      input: {
        limits: {},
      },
    },
    dirty: true,
    updatingSettings: false,
    max_score: 1,
    results: null,
    outputs: {},
    currentCase: '',
    logs: '',
    compilerOutput: '',
  },
  getters: {
    alias(state) {
      return state.alias;
    },
    showSubmitButton(state) {
      return state.showSubmitButton;
    },
    languages(state) {
      return state.languages;
    },
    sessionStorageSources(state) {
      return state.sessionStorageSources;
    },
    moduleName(state) {
      if (state.request.input.interactive) {
        return state.request.input.interactive.module_name;
      }
      return 'Main';
    },
    flatCaseResults(state) {
      let result = {};
      if (!state.results || !state.results.groups) return result;
      for (let group of state.results.groups) {
        for (let caseData of group.cases) {
          result[caseData.name] = caseData;
        }
      }
      return result;
    },
    currentCase(state) {
      return state.currentCase;
    },
    inputIn(state) {
      return state.request.input.cases[state.currentCase]['in'];
    },
    inputOut(state) {
      return state.request.input.cases[state.currentCase]['out'];
    },
    outputStdout(state) {
      let filename = `${state.currentCase}.out`;
      if (!state.outputs[filename]) {
        return '';
      }
      return state.outputs[filename];
    },
    outputStderr(state) {
      let filename = `${state.currentCase}.err`;
      if (!state.outputs[filename]) {
        return '';
      }
      return state.outputs[filename];
    },
    settingsCases(state) {
      let resultMap = {};
      for (let caseName in state.request.input.cases) {
        if (
          !Object.prototype.hasOwnProperty.call(
            state.request.input.cases,
            caseName,
          )
        )
          continue;
        let tokens = caseName.split('.', 2);
        if (!Object.prototype.hasOwnProperty.call(resultMap, tokens[0])) {
          resultMap[tokens[0]] = {
            Name: tokens[0],
            Cases: [],
            Weight: 0,
          };
        }
        resultMap[tokens[0]].Cases.push({
          Name: caseName,
          Weight: state.request.input.cases[caseName].weight,
        });
        resultMap[tokens[0]].Weight +=
          state.request.input.cases[caseName].weight;
      }
      let result = [];
      for (let groupName in resultMap) {
        if (!Object.prototype.hasOwnProperty.call(resultMap, groupName))
          continue;
        resultMap[groupName].Cases.sort((a, b) => {
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
    'request.source'(state) {
      return state.request.source;
    },
    'request.language'(state) {
      return state.request.language;
    },
    'request.input.validator.custom_validator.language'(state) {
      if (!state.request.input.validator.custom_validator) return '';
      return state.request.input.validator.custom_validator.language;
    },
    'request.input.validator.custom_validator.source'(state) {
      if (!state.request.input.validator.custom_validator) return '';
      return state.request.input.validator.custom_validator.source;
    },
    'request.input.interactive.idl'(state) {
      if (!state.request.input.interactive) return '';
      return state.request.input.interactive.idl;
    },
    'request.input.interactive.main_source'(state) {
      if (!state.request.input.interactive) return '';
      return state.request.input.interactive.main_source;
    },
    isCustomValidator(state) {
      return !!state.request.input.validator.custom_validator;
    },
    isInteractive(state) {
      return !!state.request.input.interactive;
    },
    isUpdatingSettings(state) {
      return !!state.updatingSettings;
    },
    isDirty(state) {
      return !!state.dirty;
    },
  },
  mutations: {
    alias(state, value) {
      if (state.alias) {
        persistToSessionStorage(state.alias).flush();
      }
      state.alias = value;
      const itemString = sessionStorage.getItem(
        `ephemeral-sources-${state.alias}`,
      );
      state.sessionStorageSources = null;
      if (itemString) {
        state.sessionStorageSources = JSON.parse(itemString);
      }
      if (!state.sessionStorageSources) {
        if (state.request.input.interactive) {
          state.sessionStorageSources = {
            language: languageSelectElement.value,
            sources: {
              ...interactiveTemplates,
            },
          };
        } else {
          state.sessionStorageSources = {
            language: languageSelectElement.value,
            sources: {
              ...sourceTemplates,
            },
          };
        }
      }
      state.request.language = state.sessionStorageSources.language;
      state.request.source =
        state.sessionStorageSources.sources[
          languageExtensionMapping[state.sessionStorageSources.language]
        ];
      languageSelectElement.value = state.request.language;
    },
    showSubmitButton(state, value) {
      state.problemsetId = value;
      const submitButton = document.querySelector('button[data-submit-button]');
      if (value) {
        submitButton.classList.remove('d-none');
      } else {
        submitButton.classList.add('d-none');
      }
    },
    languages(state, value) {
      state.languages = value;
      document
        .querySelectorAll('select[data-language-select] option')
        .forEach((option) => {
          if (!state.languages.includes(option.value)) {
            option.classList.add('d-none');
          } else {
            option.classList.remove('d-none');
          }
        });
    },
    currentCase(state, value) {
      state.currentCase = value;
    },
    compilerOutput(state, value) {
      state.compilerOutput = value;
    },
    logs(state, value) {
      state.logs = value;
    },
    request(state, value) {
      Vue.set(state, 'request', value);
    },
    'request.language'(state, value) {
      if (state.request.language == value) {
        return;
      }
      state.request.language = value;
      if (
        Object.prototype.hasOwnProperty.call(languageExtensionMapping, value)
      ) {
        const language = languageExtensionMapping[value];
        if (state.sessionStorageSources) {
          if (
            Object.prototype.hasOwnProperty.call(
              state.sessionStorageSources.sources,
              language,
            )
          ) {
            state.request.source =
              state.sessionStorageSources.sources[language];
          }
        } else if (store.getters.isInteractive) {
          if (
            Object.prototype.hasOwnProperty.call(interactiveTemplates, language)
          ) {
            state.request.source = interactiveTemplates[language];
          }
        } else {
          if (Object.prototype.hasOwnProperty.call(sourceTemplates, language)) {
            state.request.source = sourceTemplates[language];
          }
        }
        if (state.sessionStorageSources && !state.updatingSettings) {
          state.sessionStorageSources.language = value;
          persistToSessionStorage(state.alias)({
            alias: state.alias,
            contents: state.sessionStorageSources,
          });
        }
      }
      state.dirty = true;
    },
    'request.source'(state, value) {
      state.request.source = value;
      if (!state.updatingSettings && state.sessionStorageSources) {
        state.sessionStorageSources.sources[
          languageExtensionMapping[state.sessionStorageSources.language]
        ] = value;
        persistToSessionStorage(state.alias)({
          alias: state.alias,
          contents: state.sessionStorageSources,
        });
      }
      state.dirty = true;
    },
    inputIn(state, value) {
      state.request.input.cases[state.currentCase]['in'] = value;
      state.dirty = true;
    },
    inputOut(state, value) {
      state.request.input.cases[state.currentCase].out = value;
      state.dirty = true;
    },
    results(state, value) {
      Vue.set(state, 'results', value);
      state.dirty = false;
    },
    clearOutputs(state) {
      Vue.set(state, 'outputs', {});
    },
    output(state, payload) {
      Vue.set(state.outputs, payload.name, payload.contents);
    },
    'request.input.validator.custom_validator.source'(state, value) {
      if (!state.request.input.validator.custom_validator) return;
      state.request.input.validator.custom_validator.source = value;
      state.dirty = true;
    },
    'request.input.interactive.idl'(state, value) {
      if (!state.request.input.interactive) return;
      state.request.input.interactive.idl = value;
      state.dirty = true;
    },
    'request.input.interactive.main_source'(state, value) {
      if (!state.request.input.interactive) return;
      state.request.input.interactive.main_source = value;
      state.dirty = true;
    },

    TimeLimit(state, value) {
      state.request.input.limits.TimeLimit = value;
      state.dirty = true;
    },
    OverallWallTimeLimit(state, value) {
      state.request.input.limits.OverallWallTimeLimit = value;
      state.dirty = true;
    },
    ExtraWallTime(state, value) {
      state.request.input.limits.ExtraWallTime = value;
      state.dirty = true;
    },
    MemoryLimit(state, value) {
      state.request.input.limits.MemoryLimit = value;
      state.dirty = true;
    },
    OutputLimit(state, value) {
      state.request.input.limits.OutputLimit = value;
      state.dirty = true;
    },
    Validator(state, value) {
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
      state.request.input.validator.name = value;
      state.dirty = true;
    },
    Tolerance(state, value) {
      state.request.input.validator.tolerance = value;
      state.dirty = true;
    },
    ValidatorLanguage(state, value) {
      state.request.input.validator.custom_validator.language = value;
      state.dirty = true;
    },
    Interactive(state, value) {
      if (value) {
        if (state.request.input.interactive) return;
        Vue.set(state.request.input, 'interactive', {
          idl: defaultInteractiveIdlSource,
          module_name: 'sumas',
          language: 'cpp17-gcc',
          main_source: defaultInteractiveMainSource,
        });
      } else {
        if (!state.request.input.interactive) return;
        Vue.delete(state.request.input, 'interactive');
      }
      state.dirty = true;
    },
    InteractiveLanguage(state, value) {
      if (value == 'cpp') value = 'cpp17-gcc';
      state.request.input.interactive.language = value;
      state.dirty = true;
    },
    InteractiveModuleName(state, value) {
      state.request.input.interactive.module_name = value;
      state.dirty = true;
    },
    updatingSettings(state, value) {
      state.updatingSettings = value;
    },

    createCase(state, caseData) {
      if (
        !Object.prototype.hasOwnProperty.call(
          state.request.input.cases,
          caseData.name,
        )
      ) {
        Vue.set(state.request.input.cases, caseData.name, {
          in: '',
          out: '',
          weight: 1,
        });
      }
      state.request.input.cases[caseData.name].weight = caseData.weight;
      state.currentCase = caseData.name;
      state.dirty = true;
    },
    removeCase(state, name) {
      if (
        !Object.prototype.hasOwnProperty.call(state.request.input.cases, name)
      )
        return;

      let keys = Object.keys(store.state.request.input.cases);

      // do not delete if there is only one test case
      if (keys.length === 1) {
        return;
      }

      // switch to a random case
      let caseName = keys[0] === name ? keys[1] : keys[0];
      store.commit('currentCase', caseName);

      Vue.delete(state.request.input.cases, name);
      state.dirty = true;
    },
    reset(state) {
      Vue.set(state, 'request', {
        source: sourceTemplates.cpp,
        language: 'cpp17-gcc',
        input: {
          limits: {
            TimeLimit: 1.0, // 1s
            MemoryLimit: 67108864, // 64MB
            OverallWallTimeLimit: 5.0, // 5s
            ExtraWallTime: 0, // 0s
            OutputLimit: 10240, // 10k
          },
          validator: {
            name: 'token-caseless',
          },
          cases: {
            sample: {
              in: '1 2\n',
              out: '3\n',
              weight: 1,
            },
            long: {
              in: '123456789012345678 123456789012345678\n',
              out: '246913578024691356\n',
              weight: 1,
            },
          },
          interactive: undefined,
        },
      });
      state.result = null;
      state.max_score = 1;
      Vue.set(state, 'outputs', {});
      state.currentCase = 'sample';
      state.logs = '';
      state.compilerOutput = '';
      state.updatingSettings = false;
      state.dirty = true;
    },
  },
  strict: true,
});
// reset locally inside this file instead
// because we may forget to do it outside
store.commit('reset');
export default store;
