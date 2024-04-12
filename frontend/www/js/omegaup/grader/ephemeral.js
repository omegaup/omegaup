'use strict';

import JSZip from 'jszip';
import Vue from 'vue';
import Vuex from 'vuex';
import pako from 'pako';

import * as Util from './util';
import CaseSelectorComponent from './CaseSelectorComponent.vue';
import MonacoDiffComponent from './MonacoDiffComponent.vue';
import MonacoEditorComponent from './MonacoEditorComponent.vue';
import SettingsComponent from './SettingsComponent.vue';
import TextEditorComponent from './TextEditorComponent.vue';
import ZipViewerComponent from './ZipViewerComponent.vue';

const isEmbedded = window.location.search.indexOf('embedded') !== -1;
const theme = document.getElementById('theme').value;
const defaultValidatorSource = `#!/usr/bin/python3
# -*- coding: utf-8 -*-

import logging
import sys

def _main() -> None:
  # lee "data.in" para obtener la entrada original.
  with open('data.in', 'r') as f:
    a, b = [int(x) for x in f.read().strip().split()]
  # lee "data.out" para obtener la salida esperada.
  with open('data.out', 'r') as f:
    suma = int(f.read().strip())

  score = 0
  try:
    # Lee la salida del concursante
    suma_concursante = int(input().strip())

    # Determina si la salida es correcta
    if suma_concursante != suma:
      # Cualquier cosa que imprimas a sys.stderr se ignora, pero es útil
      # para depurar con debug-rejudge.
      logging.error('Salida incorrecta')
      return
    score = 1
  except:
    log.exception('Error leyendo la salida del concursante')
  finally:
    print(score)

if __name__ == '__main__':
  _main()`;

const defaultInteractiveIdlSource = `interface Main {
};

interface sumas {
    long sumas(long a, long b);
};`;

const defaultInteractiveMainSource = `#include <iostream>

#include "sumas.h"

int main(int argc, char* argv[]) {
    long long a, b;
    std::cin >> a >> b;
    std::cout << sumas(a, b) << '\\n';
}`;

const sourceTemplates = {
  c: `#include <stdio.h>
#include <stdint.h>

int main() {
  // TODO: fixme.

  return 0;
}`,
  cpp: `#include <iostream>

int main() {
  std::cin.tie(nullptr);
  std::ios_base::sync_with_stdio(false);

  // TODO: fixme.

  return 0;
}`,
  cs: `using System.Collections.Generic;
using System.Linq;
using System;

class Program
{
  static void Main(string[] args)
  {
    // TODO: fixme.
  }
}`,
  java: `import java.io.BufferedReader;
import java.io.IOException;
import java.io.InputStreamReader;

public class Main {
  public static void main(String[] args) throws IOException {
    BufferedReader br = new BufferedReader(
                          new InputStreamReader(System.in));
    // TODO: fixme.
  }
}`,
  lua: `-- TODO: fixme.`,
  py: `#!/usr/bin/python3

def _main() -> None:
  # TODO: fixme.
  pass

if __name__ == '__main__':
  _main()`,
  rb: `# TODO: fixme.`,
};

const originalInteractiveTemplates = {
  c: `#include "sumas.h"

long long sumas(long long a, long long b) {
  // FIXME
  return 0;
}`,
  cpp: `#include "sumas.h"

long long sumas(long long a, long long b) {
  // FIXME
  return 0;
}`,
  cs: '// not supported',
  java: `public class sumas {
  public static long sumas(long a, long b) {
    // FIXME
    return 0;
  }
}`,
  lua: '-- not supported',
  pas: `unit sumas;
{
 unit Main;
}

interface
  function sumas(a: LongInt; b: LongInt): LongInt;

implementation

uses Main;

function sumas(a: LongInt; b: LongInt): LongInt;
begin
  { FIXME }
  sumas := 0;
end;

end.`,
  py: `#!/usr/bin/python3

import Main

def sumas(a: int, b: int) -> int:
    """ sumas """
    # FIXME
    return 0`,
  rb: '# not supported',
};
const interactiveTemplates = { ...originalInteractiveTemplates };
const languageExtensionMapping = Object.fromEntries(
  Object.entries(Util.supportedLanguages).map(([key, value]) => [
    key,
    value.extension,
  ]),
);

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
          // not all languages are interactive implpied by interactive templates above
          // leave it as default 'cpp17-gcc'
          state.sessionStorageSources = {
            language: 'cpp17-gcc',
            sources: {
              ...interactiveTemplates,
            },
          };
        } else {
          state.sessionStorageSources = {
            language: document.getElementById('language').value,
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
      document.getElementById('language').value = state.request.language;
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
store.commit('reset');

const goldenLayoutSettings = {
  settings: {
    showPopoutIcon: false,
  },
  content: [
    {
      type: 'row',
      content: [
        {
          type: 'column',
          id: 'main-column',
          content: [
            {
              type: 'stack',
              id: 'source-and-settings',
              content: [
                {
                  type: 'component',
                  componentName: 'monaco-editor-component',
                  componentState: {
                    storeMapping: {
                      contents: 'request.source',
                      language: 'request.language',
                      module: 'moduleName',
                    },
                    theme,
                  },
                  id: 'source',
                  isClosable: false,
                },
                {
                  type: 'component',
                  componentName: 'settings-component',
                  id: 'settings',
                  componentState: {
                    storeMapping: {},
                    id: 'settings',
                  },
                  isClosable: false,
                },
              ],
            },
            {
              type: 'stack',
              content: [
                {
                  type: 'component',
                  componentName: 'text-editor-component',
                  componentState: {
                    storeMapping: {
                      contents: 'compilerOutput',
                    },
                    id: 'compiler',
                    readOnly: true,
                    module: 'compiler',
                    extension: 'out/err',
                    theme,
                  },
                  isClosable: false,
                },
                {
                  type: 'component',
                  componentName: 'text-editor-component',
                  componentState: {
                    storeMapping: {
                      contents: 'logs',
                    },
                    id: 'logs',
                    readOnly: true,
                    module: 'logs',
                    extension: 'txt',
                    theme,
                  },
                  isClosable: false,
                },
                {
                  type: 'component',
                  componentName: 'zip-viewer-component',
                  componentState: {
                    storeMapping: {},
                    id: 'zipviewer',
                    theme,
                  },
                  title: 'files.zip',
                  isClosable: false,
                },
              ],
              height: 20,
            },
          ],
          isClosable: false,
        },
        {
          type: 'column',
          id: 'cases-column',
          content: [
            {
              type: 'row',
              content: [
                {
                  type: 'component',
                  componentName: 'text-editor-component',
                  componentState: {
                    storeMapping: {
                      contents: 'inputIn',
                      module: 'currentCase',
                    },
                    id: 'in',
                    readOnly: false,
                    extension: 'in',
                    theme,
                  },
                  isClosable: false,
                },
                {
                  type: 'component',
                  componentName: 'text-editor-component',
                  componentState: {
                    storeMapping: {
                      contents: 'inputOut',
                      module: 'currentCase',
                    },
                    id: 'out',
                    readOnly: false,
                    extension: 'out',
                    theme,
                  },
                  isClosable: false,
                },
              ],
            },
            {
              type: 'stack',
              content: [
                {
                  type: 'component',
                  componentName: 'text-editor-component',
                  componentState: {
                    storeMapping: {
                      contents: 'outputStdout',
                      module: 'currentCase',
                    },
                    id: 'stdout',
                    readOnly: false,
                    extension: 'out',
                    theme,
                  },
                  isClosable: false,
                },
                {
                  type: 'component',
                  componentName: 'text-editor-component',
                  componentState: {
                    storeMapping: {
                      contents: 'outputStderr',
                      module: 'currentCase',
                    },
                    id: 'stderr',
                    readOnly: false,
                    extension: 'err',
                    theme,
                  },
                  isClosable: false,
                },
                {
                  type: 'component',
                  componentName: 'monaco-diff-component',
                  componentState: {
                    storeMapping: {
                      originalContents: 'inputOut',
                      modifiedContents: 'outputStdout',
                    },
                    id: 'diff',
                    theme,
                  },
                  isClosable: false,
                },
              ],
            },
          ],
          isClosable: false,
        },
        {
          type: 'component',
          id: 'case-selector-column',
          componentName: 'case-selector-component',
          componentState: {
            storeMapping: {
              cases: 'request.input.cases',
              currentCase: 'currentCase',
            },
            id: 'source',
            theme,
          },
          title: 'cases/',
          width: 15,
          isClosable: false,
        },
      ],
    },
  ],
};
const validatorSettings = {
  type: 'component',
  componentName: 'monaco-editor-component',
  componentState: {
    storeMapping: {
      contents: 'request.input.validator.custom_validator.source',
      language: 'request.input.validator.custom_validator.language',
    },
    initialModule: 'validator',
    theme,
  },
  id: 'validator',
  isClosable: false,
};
const interactiveIdlSettings = {
  type: 'component',
  componentName: 'monaco-editor-component',
  componentState: {
    storeMapping: {
      contents: 'request.input.interactive.idl',
      module: 'request.input.interactive.module_name',
    },
    initialLanguage: 'idl',
    readOnly: isEmbedded,
    theme,
  },
  id: 'interactive-idl',
  isClosable: false,
};
const interactiveMainSourceSettings = {
  type: 'component',
  componentName: 'monaco-editor-component',
  componentState: {
    storeMapping: {
      contents: 'request.input.interactive.main_source',
      language: 'request.input.interactive.language',
    },
    initialModule: 'Main',
    theme,
  },
  id: 'interactive-main-source',
  isClosable: false,
};

// eslint-disable-next-line no-undef
const layout = new GoldenLayout(
  goldenLayoutSettings,
  document.getElementById('layout-root'),
);

function RegisterVueComponent(layout, componentName, component, componentMap) {
  layout.registerComponent(componentName, function (container, componentState) {
    container.on('open', () => {
      let vueComponents = {};
      vueComponents[componentName] = component;
      let props = {
        store: store,
        storeMapping: componentState.storeMapping,
      };
      for (let k in componentState) {
        if (k == 'id') continue;
        if (!Object.prototype.hasOwnProperty.call(componentState, k)) continue;
        props[k] = componentState[k];
      }
      let vue = new Vue({
        el: container.getElement()[0],
        render: function (createElement) {
          return createElement(componentName, {
            props: props,
          });
        },
        components: vueComponents,
      });
      let vueComponent = vue.$children[0];
      if (vueComponent.title) {
        container.setTitle(vueComponent.title);
        vueComponent.$watch('title', function (title) {
          container.setTitle(title);
        });
      }
      if (vueComponent.onResize) {
        container.on('resize', () => vueComponent.onResize());
      }
      componentMap[componentState.id] = vueComponent;
    });
  });
}

let componentMapping = {};
RegisterVueComponent(
  layout,
  'case-selector-component',
  CaseSelectorComponent,
  componentMapping,
);
RegisterVueComponent(
  layout,
  'monaco-editor-component',
  MonacoEditorComponent,
  componentMapping,
);
RegisterVueComponent(
  layout,
  'monaco-diff-component',
  MonacoDiffComponent,
  componentMapping,
);
RegisterVueComponent(
  layout,
  'settings-component',
  SettingsComponent,
  componentMapping,
);
RegisterVueComponent(
  layout,
  'text-editor-component',
  TextEditorComponent,
  componentMapping,
);
RegisterVueComponent(
  layout,
  'zip-viewer-component',
  ZipViewerComponent,
  componentMapping,
);

const persistToSessionStorage = Util.throttle(({ alias, contents }) => {
  sessionStorage.setItem(
    `ephemeral-sources-${alias}`,
    JSON.stringify(contents),
  );
}, 10000);

function initialize() {
  layout.init();

  let sourceAndSettings = layout.root.getItemsById('source-and-settings')[0];
  if (store.getters.isCustomValidator) {
    const activeContentItem = sourceAndSettings.getActiveContentItem();
    sourceAndSettings.addChild(validatorSettings);
    if (activeContentItem) {
      sourceAndSettings.setActiveContentItem(activeContentItem);
    }
  }
  store.watch(
    Object.getOwnPropertyDescriptor(store.getters, 'isCustomValidator').get,
    function (value) {
      if (value) {
        const activeContentItem = sourceAndSettings.getActiveContentItem();
        sourceAndSettings.addChild(validatorSettings);
        if (activeContentItem) {
          sourceAndSettings.setActiveContentItem(activeContentItem);
        }
      } else {
        layout.root.getItemsById(validatorSettings.id)[0].remove();
      }
    },
  );
  if (store.getters.isInteractive) {
    const activeContentItem = sourceAndSettings.getActiveContentItem();
    sourceAndSettings.addChild(interactiveIdlSettings);
    sourceAndSettings.addChild(interactiveMainSourceSettings);
    if (activeContentItem) {
      sourceAndSettings.setActiveContentItem(activeContentItem);
    }
  }
  store.watch(
    Object.getOwnPropertyDescriptor(store.getters, 'isInteractive').get,
    function (value) {
      if (value) {
        const activeContentItem = sourceAndSettings.getActiveContentItem();
        sourceAndSettings.addChild(interactiveIdlSettings);
        sourceAndSettings.addChild(interactiveMainSourceSettings);
        if (activeContentItem) {
          sourceAndSettings.setActiveContentItem(activeContentItem);
        }
      } else {
        layout.root.getItemsById(interactiveIdlSettings.id)[0].remove();
        layout.root.getItemsById(interactiveMainSourceSettings.id)[0].remove();
      }
    },
  );

  if (isEmbedded) {
    // Embedded layout should not be able to modify the settings.
    layout.root.getItemsById('settings')[0].remove();
    document.getElementById('download').style.display = 'none';
    document.getElementById('upload').style.display = 'none';
    document.querySelector('label[for="upload"]').style.display = 'none';

    // Since the embedded grader has a lot less horizontal space available, we
    // move the first two columns into a stack so they can be switched between.
    let mainColumn = layout.root.getItemsById('main-column')[0];
    let casesColumn = layout.root.getItemsById('cases-column')[0];
    let caseSelectorColumn = layout.root.getItemsById(
      'case-selector-column',
    )[0];
    let oldWidth = caseSelectorColumn.element[0].clientWidth;
    let oldHeight = caseSelectorColumn.element[0].clientHeight;

    let newStack = layout.createContentItem({
      type: 'stack',
      content: [],
      isClosable: false,
    });

    mainColumn.parent.addChild(newStack, 0);

    casesColumn.setTitle('cases');
    casesColumn.parent.removeChild(casesColumn, true);
    newStack.addChild(casesColumn, 0);

    mainColumn.setTitle('code');
    mainColumn.parent.removeChild(mainColumn, true);
    newStack.addChild(mainColumn, 0);

    // Also extend the case selector column a little bit so that it looks nicer.
    caseSelectorColumn.container.setSize(Math.max(160, oldWidth), oldHeight);

    // Whenever a case is selected, show the cases tab.
    store.watch(
      Object.getOwnPropertyDescriptor(store.getters, 'currentCase').get,
      (value) => {
        if (store.getters.isUpdatingSettings) return;
        casesColumn.parent.setActiveContentItem(casesColumn);
      },
    );
  }
}

function onResized() {
  const layoutRoot = document.getElementById('layout-root');
  if (!layoutRoot.clientWidth) return;
  if (!layout.isInitialised) {
    initialize();
  }
  layout.updateSize();
}

if (window.ResizeObserver) {
  new ResizeObserver(onResized).observe(document.getElementById('layout-root'));
} else {
  window.addEventListener('resize', onResized);
}
onResized();

document.getElementById('language').addEventListener('change', function () {
  store.commit('request.language', this.value);
});

function onDetailsJsonReady(results) {
  store.commit('results', results);
  store.commit('compilerOutput', results.compile_error || '');
}

function onFilesZipReady(blob) {
  if (blob == null || blob.size == 0) {
    if (componentMapping.zipviewer) {
      componentMapping.zipviewer.zip = null;
    }
    store.commit('clearOutputs');
    return;
  }
  let reader = new FileReader();
  reader.addEventListener('loadend', (e) => {
    if (e.target.readyState != FileReader.DONE) return;
    JSZip.loadAsync(reader.result)
      .then((zip) => {
        if (componentMapping.zipviewer) {
          componentMapping.zipviewer.zip = zip;
        }
        store.commit('clearOutputs');
        Promise.all([
          zip.file('Main/compile.err').async('string'),
          zip.file('Main/compile.out').async('string'),
        ])
          .then((values) => {
            for (let value of values) {
              if (!value) continue;
              store.commit('compilerOutput', value);
              return;
            }
            store.commit('compilerOutput', '');
          })
          .catch(Util.asyncError);
        for (let filename in zip.files) {
          if (filename.indexOf('/') !== -1) continue;
          zip
            .file(filename)
            .async('string')
            .then((contents) => {
              store.commit('output', {
                name: filename,
                contents: contents,
              });
            })
            .catch(Util.asyncError);
        }
      })
      .catch(Util.asyncError);
  });
  reader.readAsArrayBuffer(blob);
}

store.watch(
  Object.getOwnPropertyDescriptor(store.getters, 'isDirty').get,
  function (value) {
    let downloadLabelElement = document.getElementById('download-label');
    if (!value || !downloadLabelElement) return;

    if (downloadLabelElement.className.indexOf('fa-download') == -1) return;
    downloadLabelElement.className = downloadLabelElement.className.replace(
      'fa-download',
      'fa-file-archive-o',
    );
    let downloadElement = document.getElementById('download');
    downloadElement.download = undefined;
    downloadElement.href = undefined;
  },
);

document.getElementById('upload').addEventListener('change', (e) => {
  let files = e.target.files;
  if (!files.length) return;

  let reader = new FileReader();
  reader.addEventListener('loadend', (e) => {
    if (e.target.readyState != FileReader.DONE) return;
    JSZip.loadAsync(reader.result)
      .then((zip) => {
        store.commit('reset');
        store.commit('removeCase', 'long');
        let cases = {};
        for (let fileName in zip.files) {
          if (!Object.prototype.hasOwnProperty.call(zip.files, fileName))
            continue;

          if (fileName.startsWith('cases/') && fileName.endsWith('.in')) {
            let caseName = fileName.substring(
              'cases/'.length,
              fileName.length - '.in'.length,
            );
            cases[caseName] = true;
            let caseOutFileName = `cases/${caseName}.out`;
            if (
              !Object.prototype.hasOwnProperty.call(zip.files, caseOutFileName)
            )
              continue;
            store.commit('createCase', {
              name: caseName,
              weight: 1,
            });

            zip
              .file(fileName)
              .async('string')
              .then((value) => {
                store.commit('currentCase', caseName);
                store.commit('inputIn', value);
              })
              .catch(Util.asyncError);
            zip
              .file(caseOutFileName)
              .async('string')
              .then((value) => {
                store.commit('currentCase', caseName);
                store.commit('inputOut', value);
              })
              .catch(Util.asyncError);
          } else if (fileName.startsWith('validator.')) {
            let extension = fileName.substring('validator.'.length);
            if (
              !Object.prototype.hasOwnProperty.call(
                languageExtensionMapping,
                extension,
              )
            )
              continue;
            zip
              .file(fileName)
              .async('string')
              .then((value) => {
                store.commit('Validator', 'custom');
                store.commit('ValidatorLanguage', extension);
                store.commit(
                  'request.input.validator.custom_validator.source',
                  value,
                );
              })
              .catch(Util.asyncError);
          } else if (
            fileName.startsWith('interactive/') &&
            fileName.endsWith('.idl')
          ) {
            let moduleName = fileName.substring(
              'interactive/'.length,
              fileName.length - '.idl'.length,
            );
            zip
              .file(fileName)
              .async('string')
              .then((value) => {
                store.commit('Interactive', true);
                store.commit('InteractiveModuleName', moduleName);
                store.commit('request.input.interactive.idl', value);
              })
              .catch(Util.asyncError);
          } else if (fileName.startsWith('interactive/Main.')) {
            let extension = fileName.substring('interactive/Main.'.length);
            if (
              !Object.prototype.hasOwnProperty.call(
                languageExtensionMapping,
                extension,
              )
            )
              continue;
            zip
              .file(fileName)
              .async('string')
              .then((value) => {
                store.commit('Interactive', true);
                store.commit('InteractiveLanguage', extension);
                store.commit('request.input.interactive.main_source', value);
              })
              .catch(Util.asyncError);
          }
        }

        if (Object.prototype.hasOwnProperty.call(zip.files, 'testplan')) {
          zip
            .file('testplan')
            .async('string')
            .then((value) => {
              for (let line of value.split('\n')) {
                if (line.startsWith('#') || line.trim() == '') continue;
                let tokens = line.split(/\s+/);
                if (tokens.length != 2) continue;
                let [caseName, weight] = tokens;
                if (!Object.prototype.hasOwnProperty.call(cases, caseName))
                  continue;
                store.commit('createCase', {
                  name: caseName,
                  weight: parseFloat(weight),
                });
              }
            })
            .catch(Util.asyncError);
        }
        if (Object.prototype.hasOwnProperty.call(zip.files, 'settings.json')) {
          zip
            .file('settings.json')
            .async('string')
            .then((value) => {
              value = JSON.parse(value);
              if (Object.prototype.hasOwnProperty.call(value, 'Limits')) {
                for (let name of [
                  'TimeLimit',
                  'OverallWallTimeLimit',
                  'ExtraWallTime',
                  'MemoryLimit',
                  'OutputLimit',
                ]) {
                  if (!Object.prototype.hasOwnProperty.call(value.Limits, name))
                    continue;
                  store.commit(name, value.Limits[name]);
                }
              }
              if (Object.prototype.hasOwnProperty.call(value, 'Validator')) {
                if (
                  Object.prototype.hasOwnProperty.call(value.Validator, 'Name')
                ) {
                  store.commit('Validator', value.Validator.Name);
                }
                if (
                  Object.prototype.hasOwnProperty.call(
                    value.Validator,
                    'Tolerance',
                  )
                ) {
                  store.commit('Tolerance', value.Validator.Tolerance);
                }
              }
            })
            .catch(Util.asyncError);
        }
      })
      .catch(Util.asyncError);
  });
  reader.readAsArrayBuffer(files[0]);
});

document.getElementById('download').addEventListener('click', (e) => {
  let downloadLabelElement = document.getElementById('download-label');
  if (downloadLabelElement.className.indexOf('fa-download') != -1) return true;
  e.preventDefault();

  let zip = new JSZip();
  let cases = zip.folder('cases');

  let testplan = '';
  for (let caseName in store.state.request.input.cases) {
    if (
      !Object.prototype.hasOwnProperty.call(
        store.state.request.input.cases,
        caseName,
      )
    )
      continue;

    cases.file(`${caseName}.in`, store.state.request.input.cases[caseName].in);
    cases.file(
      `${caseName}.out`,
      store.state.request.input.cases[caseName].out,
    );
    testplan +=
      caseName + ' ' + store.state.request.input.cases[caseName].weight + '\n';
  }
  zip.file('testplan', testplan);
  let settingsValidator = {
    Name: store.state.request.input.validator.name,
  };
  if (
    Object.prototype.hasOwnProperty.call(
      store.state.request.input.validator,
      'tolerance',
    )
  ) {
    settingsValidator.Tolerance = store.state.request.input.validator.Tolerance;
  }
  if (
    Object.prototype.hasOwnProperty.call(
      store.state.request.input.validator,
      'custom_validator',
    )
  ) {
    settingsValidator.Lang =
      store.state.request.input.validator.custom_validator.lang;
  }
  zip.file(
    'settings.json',
    JSON.stringify(
      {
        Cases: store.getters.settingsCases,
        Limits: store.state.request.input.limits,
        Validator: settingsValidator,
      },
      null,
      '  ',
    ),
  );

  let interactive = store.state.request.input.interactive;
  if (interactive) {
    let interactiveFolder = zip.folder('interactive');
    interactiveFolder.file(`${interactive.module_name}.idl`, interactive.idl);
    interactiveFolder.file(
      `Main.${interactive.language}`,
      interactive.main_source,
    );
    interactiveFolder.file(
      'examples/sample.in',
      store.state.request.input.cases.sample.in,
    );
  }

  let customValidator = store.state.request.input.validator.custom_validator;
  if (customValidator) {
    zip.file('validator.' + customValidator.language, customValidator.source);
  }

  zip
    .generateAsync({ type: 'blob' })
    .then((blob) => {
      downloadLabelElement.className = downloadLabelElement.className.replace(
        'fa-file-archive-o',
        'fa-download',
      );
      let downloadElement = document.getElementById('download');
      downloadElement.download = 'omegaup.zip';
      downloadElement.href = window.URL.createObjectURL(blob);
    })
    .catch(Util.asyncError);
});

const submitButton = document.querySelector('button[data-submit-button]');
document
  .querySelector('form.ephemeral-form')
  .addEventListener('submit', (e) => {
    e.preventDefault();
    submitButton.setAttribute('disabled', '');
    parent.postMessage({
      method: 'submitRun',
      params: {
        problem_alias: store.state.alias,
        language: store.state.request.language,
        source: store.state.request.source,
      },
    });
    submitButton.removeAttribute('disabled');
  });

const runButton = document.querySelector('button[data-run-button]');
runButton.addEventListener('click', () => {
  runButton.setAttribute('disabled', '');
  fetch('run/new/', {
    method: 'POST',
    headers: new Headers({
      'Content-Type': 'application/json',
    }),
    body: JSON.stringify(store.state.request),
  })
    .then((response) => {
      if (!response.ok) return null;
      history.replaceState(
        undefined,
        undefined,
        '#' + response.headers.get('X-OmegaUp-EphemeralToken'),
      );
      return response.formData();
    })
    .then((formData) => {
      runButton.removeAttribute('disabled');
      if (!formData) {
        onDetailsJsonReady({
          verdict: 'JE',
          contest_score: 0,
          max_score: this.state.max_score,
        });
        store.commit('logs', '');
        onFilesZipReady(null);
        return;
      }

      if (formData.has('details.json')) {
        let reader = new FileReader();
        reader.addEventListener('loadend', function () {
          onDetailsJsonReady(JSON.parse(reader.result));
        });
        reader.readAsText(formData.get('details.json'));
      }

      if (formData.has('logs.txt.gz')) {
        let reader = new FileReader();
        reader.addEventListener('loadend', function () {
          if (reader.result.byteLength == 0) {
            store.commit('logs', '');
            return;
          }

          store.commit(
            'logs',
            new TextDecoder('utf-8').decode(pako.inflate(reader.result)),
          );
        });
        reader.readAsArrayBuffer(formData.get('logs.txt.gz'));
      } else {
        store.commit('logs', '');
      }

      onFilesZipReady(formData.get('files.zip'));
    })
    .catch(Util.asyncError);
});

function setSettings({ alias, settings, languages, showSubmitButton }) {
  if (!settings) {
    return;
  }
  if (settings.interactive) {
    for (let language in settings.interactive.templates) {
      if (
        Object.prototype.hasOwnProperty.call(
          settings.interactive.templates,
          language,
        )
      ) {
        interactiveTemplates[language] =
          settings.interactive.templates[language];
      } else {
        interactiveTemplates[language] = originalInteractiveTemplates[language];
      }
    }
  }
  store.commit('languages', languages);
  store.commit('updatingSettings', true);
  store.commit('reset');
  store.commit('Interactive', !!settings.interactive);
  store.commit('alias', alias);
  store.commit('showSubmitButton', showSubmitButton);
  store.commit('removeCase', 'long');
  store.commit('MemoryLimit', settings.limits.MemoryLimit * 1024);
  store.commit('OutputLimit', settings.limits.OutputLimit);
  for (let name of ['TimeLimit', 'OverallWallTimeLimit', 'ExtraWallTime']) {
    if (!Object.prototype.hasOwnProperty.call(settings.limits, name)) continue;
    store.commit(name, Util.parseDuration(settings.limits[name]));
  }
  store.commit('Validator', settings.validator.name);
  store.commit('Tolerance', settings.validator.tolerance);

  if (settings.interactive) {
    store.commit('InteractiveLanguage', settings.interactive.language);
    store.commit('InteractiveModuleName', settings.interactive.module_name);
    store.commit('request.input.interactive.idl', settings.interactive.idl);
    store.commit(
      'request.input.interactive.main_source',
      settings.interactive.main_source,
    );
  }

  // If there are cases for a problem, then delete the sample case
  if (Object.keys(settings.cases).length) store.commit('removeCase', 'sample');

  for (let caseName in settings.cases) {
    if (!Object.prototype.hasOwnProperty.call(settings.cases, caseName))
      continue;
    let caseData = settings.cases[caseName];
    store.commit('createCase', {
      name: caseName,
      weight: caseData.weight,
    });
    store.commit('inputIn', caseData['in']);
    store.commit('inputOut', caseData.out);
  }

  // Given that the current case will change several times, schedule the
  // flag to avoid swapping into the cases view for the next tick.
  //
  // Also change to the main column in case it was not previously selected.
  setTimeout(() => {
    store.commit('updatingSettings', false);
    if (!layout.isInitialised) return;
    let mainColumn = layout.root.getItemsById('main-column')[0];
    mainColumn.parent.setActiveContentItem(mainColumn);
  });
}

// Add a message listener in case we are embedded or the embedded runner was
// popped into a full-blown tab.
window.addEventListener(
  'message',
  (e) => {
    if (e.origin != window.location.origin || !e.data) return;

    switch (e.data.method) {
      case 'setSettings':
        setSettings({
          alias: e.data.params.alias,
          settings: e.data.params.settings,
          showSubmitButton: e.data.params.showSubmitButton,
          languages: e.data.params.languages,
        });
        break;
    }
  },
  false,
);

function onHashChanged() {
  if (window.location.hash.length == 0) {
    store.commit('reset');
    store.commit('logs', '');
    onDetailsJsonReady({});
    onFilesZipReady(null);
    return;
  }

  let token = window.location.hash.substring(1);
  fetch(`run/${token}/request.json`)
    .then((response) => {
      if (!response.ok) return null;
      return response.json();
    })
    .then((request) => {
      if (!request) {
        store.commit('reset');
        store.commit('logs', '');
        onDetailsJsonReady({});
        onFilesZipReady(null);
        return;
      }
      request.input.limits.ExtraWallTime = Util.parseDuration(
        request.input.limits.ExtraWallTime,
      );
      request.input.limits.OverallWallTimeLimit = Util.parseDuration(
        request.input.limits.OverallWallTimeLimit,
      );
      request.input.limits.TimeLimit = Util.parseDuration(
        request.input.limits.TimeLimit,
      );
      if (!request.input.cases.sample) {
        // When the run was made programatically, it does not always contain
        // a sample case. In order to display those runs without crashing,
        // just create a fake entry with no weight.
        request.input.cases.sample = {
          in: '',
          out: '',
          weight: 0,
        };
      }
      store.commit('request', request);
      fetch(`run/${token}/details.json`)
        .then((response) => {
          if (!response.ok) return {};
          return response.json();
        })
        .then(onDetailsJsonReady)
        .catch(Util.asyncError);
      fetch(`run/${token}/files.zip`)
        .then((response) => {
          if (!response.ok) return null;
          return response.blob();
        })
        .then(onFilesZipReady)
        .catch(Util.asyncError);
      fetch(`run/${token}/logs.txt`)
        .then((response) => {
          if (!response.ok) return '';
          return response.text();
        })
        .then((text) => store.commit('logs', text))
        .catch(Util.asyncError);
    })
    .catch(Util.asyncError);
}
window.addEventListener('hashchange', onHashChanged, false);
onHashChanged();
