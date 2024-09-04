/* eslint-disable */
// @ts-nocheck
'use strict';

import JSZip from 'jszip';
import Vue from 'vue';
import pako from 'pako';
import GoldenLayout from 'golden-layout';

import * as Util from './util';
import CaseSelector from './CaseSelector.vue';
import DiffEditor from './DiffEditor.vue';
import IDESettings from './IDESettings.vue';
import MonacoEditor from './MonacoEditor.vue';
import TextEditor from './TextEditor.vue';
import ZipViewer from './ZipViewer.vue';

// imports from new files
import store from './GraderStore';
import { UNEMBEDDED_CONFIG, EMBEDDED_CONFIG } from './GoldenLayoutConfigs';
import {
  TEXT_EDITOR_COMPONENT_NAME,
  MONACO_DIFF_COMPONENT_NAME,
  MONACO_EDITOR_COMPONENT_NAME,
  CASE_SELECTOR_COMPONENT_NAME,
  ZIP_VIEWER_COMPONENT_NAME,
  SETTINGS_COMPONENT_NAME,
} from './GoldenLayoutConfigs';

const isEmbedded = window.location.search.indexOf('embedded') !== -1;
const theme = document.getElementById('theme').value;
let isInitialised = false;

const languageExtensionMapping = Object.fromEntries(
  Object.entries(Util.supportedLanguages).map(([key, value]) => [
    key,
    value.extension,
  ]),
);
// eslint-disable-next-line no-undef
const layout = new GoldenLayout(
  isEmbedded ? EMBEDDED_CONFIG : UNEMBEDDED_CONFIG,
  document.getElementById('layout-root'),
);

function RegisterVueComponent(layout, componentName, component, componentMap) {
  layout.registerComponent(componentName, function (container, componentState) {
    container.on('open', () => {
      let vueComponents = {};
      vueComponents[componentName] = component;
      let props = {
        storeMapping: componentState.storeMapping,
        theme: theme,
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
  CASE_SELECTOR_COMPONENT_NAME,
  CaseSelector,
  componentMapping,
);
RegisterVueComponent(
  layout,
  MONACO_EDITOR_COMPONENT_NAME,
  MonacoEditor,
  componentMapping,
);
RegisterVueComponent(
  layout,
  MONACO_DIFF_COMPONENT_NAME,
  DiffEditor,
  componentMapping,
);
RegisterVueComponent(
  layout,
  SETTINGS_COMPONENT_NAME,
  IDESettings,
  componentMapping,
);
RegisterVueComponent(
  layout,
  TEXT_EDITOR_COMPONENT_NAME,
  TextEditor,
  componentMapping,
);
RegisterVueComponent(
  layout,
  ZIP_VIEWER_COMPONENT_NAME,
  ZipViewer,
  componentMapping,
);

function initialize() {
  layout.init();
  if (isEmbedded) {
    // Embedded layout should not be able to modify the settings.
    document.getElementById('download').style.display = 'none';
    document.getElementById('upload').style.display = 'none';
    document.querySelector('label[for="upload"]').style.display = 'none';

    // Whenever a case is selected, show the cases tab.
    store.watch(
      Object.getOwnPropertyDescriptor(store.getters, 'currentCase').get,
      (value) => {
        if (store.getters.isUpdatingSettings) return;
        const casesColumn = layout.root.getItemsById('cases-column')[0];
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
        store.dispatch('reset');
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
                store.commit(
                  'request.input.validator.custom_validator.language',
                  extension,
                );
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
                store.commit('Interactive', {
                  idl: value,
                  module_name: moduleName,
                });
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
                store.commit('Interactive', {
                  language: extension,
                  main_source: value,
                });
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

// Add a message listener in case we are embedded or the embedded runner was
// popped into a full-blown tab.
window.addEventListener(
  'message',
  (e) => {
    if (e.origin != window.location.origin || !e.data) return;

    switch (e.data.method) {
      case 'setSettings':
        store.dispatch('initProblem', {
          problem: e.data.params.problem,
          initialLanguage: e.data.params.initialLanguage,
          initialSource: e.data.params.initialSource,
          languages: e.data.params.languages,
          showRunButton: e.data.params.showRunButton,
          showSubmitButton: e.data.params.showSubmitButton,
        });
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
        break;
    }
  },
  false,
);

function onHashChanged() {
  if (window.location.hash.length == 0) {
    store.dispatch('reset');
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
        store.dispatch('reset');
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
