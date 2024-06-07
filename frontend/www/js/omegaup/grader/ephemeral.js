'use strict';

import JSZip from 'jszip';
import Vue from 'vue';
import pako from 'pako';

import * as Util from './util';
import CaseSelectorComponent from './CaseSelectorComponent.vue';
import MonacoDiffComponent from './MonacoDiffComponent.vue';
import MonacoEditorComponent from './MonacoEditorComponent.vue';
import SettingsComponent from './SettingsComponent.vue';
import TextEditorComponent from './TextEditorComponent.vue';
import ZipViewerComponent from './ZipViewerComponent.vue';

// imports from new files
import * as Templates from './GraderTemplates';
import store from './GraderStore';

const isEmbedded = window.location.search.indexOf('embedded') !== -1;
const theme = document.getElementById('theme').value;
let isInitialised = false;

const originalInteractiveTemplates = {
  ...Templates.originalInteractiveTemplates,
};
const interactiveTemplates = { ...originalInteractiveTemplates };
const languageExtensionMapping = Object.fromEntries(
  Object.entries(Util.supportedLanguages).map(([key, value]) => [
    key,
    value.extension,
  ]),
);

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

function initialize() {
  layout.init();

  // is custome validator this like another mode other than
  // interactive and non interactive problems?
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
  // if we load an interactive problem from a list like a course or
  // a contest make sure to change to interactive settings
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
  if (!isInitialised) {
    initialize();
    isInitialised = true;
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
  // if there is not an alias for some reason
  // should also return early
  if (!settings || !alias) {
    return;
  }
  // this will get saved in session storage when setting alias
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
  store.commit('updatingSettings', true);

  store.commit('alias', alias);
  store.commit('languages', languages);
  store.commit('showSubmitButton', showSubmitButton);
  store.commit('limits', settings.limits);
  store.commit('Validator', settings.validator.name);
  store.commit('Tolerance', settings.validator.tolerance);

  for (let name in store.state.request.input.cases) {
    store.commit('removeCase', name);
  }
  // create sample case if there are no cases for the problem
  if (!Object.keys(settings.cases).length) {
    store.commit('createCase', {
      name: 'sample',
      in: '1 2\n',
      out: '3\n',
      weight: 1,
    });
  }
  // if the problem is interactive, we need to init remaining settings
  store.commit('Interactive', !!settings.interactive);
  if (settings.interactive) {
    store.commit('InteractiveLanguage', settings.interactive.language);
    store.commit('InteractiveModuleName', settings.interactive.module_name);
    store.commit('request.input.interactive.idl', settings.interactive.idl);
    store.commit(
      'request.input.interactive.main_source',
      settings.interactive.main_source,
    );
  }
  // create cases for current problem
  for (let caseName in settings.cases) {
    if (!Object.prototype.hasOwnProperty.call(settings.cases, caseName))
      continue;
    let caseData = settings.cases[caseName];
    store.commit('createCase', {
      name: caseName,
      weight: caseData.weight,
      in: caseData['in'],
      out: caseData['out'],
    });
  }
  // Given that the current case will change several times, schedule the
  // flag to avoid swapping into the cases view for the next tick.
  //
  // Also change to the main column in case it was not previously selected.
  setTimeout(() => {
    store.commit('updatingSettings', false);
    if (!isInitialised) return;
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
// when does this piece of code run
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
