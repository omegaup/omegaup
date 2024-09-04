<template>
  <div>
    <div class="navbar navbar-light bg-light py-0 border-bottom">
      <span class="navbar-brand">
        omegaUp ephemeral grader
        <sup>&alpha;</sup>
      </span>
      <form class="form-inline my-2 my-lg-0 ephemeral-form">
        <label v-if="isUploadButton" for="upload">
          <a class="btn btn-secondary btn-sm mr-sm-2" role="button">
            <span
              class="fa fa-upload"
              :title="T.wordsUpload"
              aria-hidden="true"
            ></span>
          </a>
        </label>
        <input
          :id="'upload'"
          type="file"
          accept=".zip"
          class="d-none"
          @change="handleUpload"
        />
        <a
          v-if="isDownloadButton"
          ref="zip-download-link"
          class="btn btn-secondary btn-sm mr-sm-2"
          role="button"
          @click="handleDownload"
        >
          <span
            ref="zip-download-icon"
            class="fa"
            :class="isDirty ? 'fa-file-archive' : 'fa-download'"
            :title="isDirty ? T.zipPrepare : T.wordsDownload"
            aria-hidden="true"
          ></span>
        </a>
        </label>

        <select
          v-model="selectedLanguage"
          class="form-control form-control-sm mr-sm-2"
          data-language-select
        >
          <option
            v-for="language in languages"
            :key="language"
            :value="language"
          >
            {{ getLanguageName(language) }}
          </option>
        </select>

        <button
          v-if="isRunButton"
          class="btn btn-sm btn-secondary mr-2 my-sm-0 ephemeral-button"
          data-run-button
          @click.prevent="handleRun"
        >
          <span>{{ T.wordsRun }}</span>
          <span
            v-if="isRunLoading"
            class="spinner-border spinner-border-sm"
            role="status"
            aria-hidden="true"
          ></span>
        </button>
        <button
          v-if="isSubmitButton"
          class="btn btn-sm btn-primary my-2 my-sm-0 ephemeral-button"
          data-submit-button
          @click.prevent="handleSubmit"
        >
          <span>{{ T.wordsSubmit }}</span>
          <span
            v-if="isSubmitLoading"
            class="spinner-border spinner-border-sm"
            role="status"
            aria-hidden="true"
          ></span>
        </button>
      </form>
    </div>
    <section ref="layout-root" class="col px-0"></section>
  </div>
</template>

<script lang="ts">
import { Component, Prop, Ref, Watch } from 'vue-property-decorator';
import Vue, { CreateElement } from 'vue';
import type { Component as VueComponent } from 'vue'; // this is the component type for Vue components
import GoldenLayout from 'golden-layout';
import JSZip from 'jszip';
import pako from 'pako';

import { types } from '../api_types';
import CaseSelector from './CaseSelector.vue';
import DiffEditor from './DiffEditor.vue';
import IDESettings from './IDESettings.vue';
import MonacoEditor from './MonacoEditor.vue';
import TextEditor from './TextEditor.vue';
import ZipViewer from './ZipViewer.vue';
import store, { GraderResults } from './GraderStore';
import * as Util from './util';
import { UNEMBEDDED_CONFIG, EMBEDDED_CONFIG } from './GoldenLayoutConfigs';
import {
  TEXT_EDITOR_COMPONENT_NAME,
  MONACO_DIFF_COMPONENT_NAME,
  MONACO_EDITOR_COMPONENT_NAME,
  CASE_SELECTOR_COMPONENT_NAME,
  ZIP_VIEWER_COMPONENT_NAME,
  SETTINGS_COMPONENT_NAME,
} from './GoldenLayoutConfigs';
import T from '../lang';

interface GraderComponent extends Vue {
  title?: string;
  onResize?: () => void;
}
interface ComponentProps {
  [key: string]: any;
}
interface ComponentState {
  [key: string]: any;
}

@Component
export default class Ephemeral extends Vue {
  @Prop({ default: true }) isEmbedded!: boolean;
  @Prop({ default: 'vs' }) theme!: string;

  @Prop() problem!: types.ProblemInfo;
  @Prop({ default: 'cpp17-gcc' }) initialLanguage!: string;
  @Prop({ default: '' }) initialSource!: string;
  @Prop({ default: () => [] }) acceptedLanguages!: string[];
  @Prop({ default: false }) canSubmit!: boolean;
  @Prop({ default: true }) canRun!: boolean;

  @Ref('layout-root') readonly layoutRoot!: HTMLElement;
  @Ref('zip-download-link') readonly zipDownloadLink!: HTMLAnchorElement;
  @Ref('zip-download-icon') readonly zipDownloadIcon!: HTMLSpanElement;

  goldenLayout: GoldenLayout | null = null;
  componentMapping: { [key: string]: VueComponent } = {};
  T = T;
  isRunLoading = false;
  isSubmitLoading = false;

  get isUploadButton() {
    return !this.isEmbedded;
  }
  get isDownloadButton() {
    return !this.isEmbedded;
  }
  get isSubmitButton() {
    return store.getters['showSubmitButton'];
  }
  get isRunButton() {
    return store.getters['showRunButton'];
  }
  get isDirty() {
    return store.getters['isDirty'];
  }

  get selectedLanguage() {
    return store.getters['request.language'];
  }
  set selectedLanguage(language: string) {
    store.dispatch('request.language', language);
  }
  getLanguageName(language: string): string {
    return Util.supportedLanguages[language].name;
  }
  get languages(): string[] {
    return store.getters['languages'];
  }
  get currentCase(): string {
    return store.getters['currentCase'];
  }

  initProblem() {
    // use commits for synchronous behavior
    // or else bugs occur where layout toggles cases column
    // when it shouldnt
    store.commit('updatingSettings', true);
    store
      .dispatch('initProblem', {
        initialLanguage: this.initialLanguage,
        languages: this.acceptedLanguages,
        problem: this.problem,
        showRunButton: this.canRun,
        showSubmitButton: this.canSubmit,
      })
      .then(() => {
        store.commit('updatingSettings', false);
        this.$nextTick(() => {
          if (!this.goldenLayout?.isInitialised) return;
          let mainColumn = this.goldenLayout.root.getItemsById(
            'main-column',
          )[0];
          mainColumn.parent.setActiveContentItem(mainColumn);
        });
      })
      .catch(Util.asyncError);
  }
  @Watch('problem')
  @Watch('initialLanguage')
  onProblemChange() {
    if (this.isEmbedded) {
      this.initProblem();
    }
  }
  @Watch('currentCase', { immediate: true })
  onCurrentCaseChange() {
    if (!this.isEmbedded || store.getters['isUpdatingSettings']) return;
    const casesColumn = this.goldenLayout?.root.getItemsById('cases-column')[0];
    if (!casesColumn) return;
    casesColumn.parent.setActiveContentItem(casesColumn);
  }
  @Watch('isDirty')
  onDirtyChange(value: boolean) {
    if (!value || this.isEmbedded) return;

    this.zipDownloadLink.removeAttribute('href');
    this.zipDownloadLink.removeAttribute('download');
  }

  onDetailsJsonReady(results: GraderResults) {
    store.dispatch('results', results);
    store.dispatch('compilerOutput', results.compile_error || '');
  }
  onFilesZipReady(blob: Blob | null) {
    if (blob == null || blob.size == 0) {
      if (this.componentMapping.zipviewer) {
        (this.componentMapping.zipviewer as ZipViewer).zip = null;
      }
      store.dispatch('clearOutputs');
      return;
    }

    const reader = new FileReader();
    reader.addEventListener('loadend', (e) => {
      if (e.target?.readyState != FileReader.DONE) return;
      if (!reader.result) return;

      JSZip.loadAsync(reader.result)
        .then((zip) => {
          if (this.componentMapping.zipviewer) {
            (this.componentMapping.zipviewer as ZipViewer).zip = zip;
          }
          store.dispatch('clearOutputs');

          Promise.all([
            zip.file('Main/compile.err')?.async('string'),
            zip.file('Main/compile.out')?.async('string'),
          ])
            .then((values) => {
              for (const value of values) {
                if (!value) continue;
                store.dispatch('compilerOutput', value);
                return;
              }
              store.dispatch('compilerOutput', '');
            })
            .catch(Util.asyncError);

          for (const filename in zip.files) {
            if (filename.indexOf('/') !== -1) continue;
            zip
              .file(filename)
              ?.async('string')
              .then((contents) => {
                store.dispatch('output', {
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

  handleSubmit() {
    postMessage({
      method: 'submitRun',
      params: {
        problem_alias: store.getters['alias'],
        language: store.getters['request.language'],
        source: store.getters['request.source'],
      },
    });
  }
  handleRun() {
    if (this.isRunLoading) return;

    this.isRunLoading = true;
    fetch(`/grader/ephemeral/run/new/`, {
      method: 'POST',
      headers: new Headers({
        'Content-Type': 'application/json',
      }),
      body: JSON.stringify(store.getters['request']),
    })
      .then((response) => {
        if (!response.ok) return null;
        return response.formData();
      })
      .then((formData) => {
        if (!formData) {
          this.onDetailsJsonReady({
            contest_score: 0,
            judged_by: 'runner',
            max_score: 0,
            score: 0,
            verdict: 'JE',
          });
          store.dispatch('logs', '');
          this.onFilesZipReady(null);
          return;
        }

        if (formData.has('details.json')) {
          const reader = new FileReader();
          reader.addEventListener('loadend', () => {
            if (!reader.result) {
              this.onDetailsJsonReady({
                contest_score: 0,
                judged_by: 'runner',
                max_score: 0,
                score: 0,
                verdict: 'JE',
              });
            } else this.onDetailsJsonReady(JSON.parse(reader.result as string));
          });
          reader.readAsText(formData.get('details.json') as File);
        }

        if (formData.has('logs.txt.gz')) {
          const reader = new FileReader();
          reader.addEventListener('loadend', function () {
            if (
              reader.result instanceof ArrayBuffer &&
              reader.result.byteLength == 0
            ) {
              store.dispatch('logs', '');
              return;
            }
            store.dispatch(
              'logs',
              new TextDecoder('utf-8').decode(
                pako.inflate(reader.result as ArrayBuffer),
              ),
            );
          });
          reader.readAsArrayBuffer(formData.get('logs.txt.gz') as File);
        } else {
          store.dispatch('logs', '');
        }

        this.onFilesZipReady(formData.get('files.zip') as File);
      })
      .catch(Util.asyncError)
      .finally(() => {
        this.isRunLoading = false;
      });
  }
  handleDownload(e: Event) {
    // the state is dirty when we need to re-configure zip file
    // if not, download the url (continue default behavior)
    if (!this.isDirty) return true;

    e.preventDefault();
    const zip = new JSZip();
    const cases = zip.folder('cases');
    if (!cases) {
      console.error('could not create cases folder');
      return;
    }

    const inputCases = store.getters['inputCases'];
    let testplan = '';
    for (const caseName in inputCases) {
      if (!inputCases[caseName]) continue;
      cases.file(`${caseName}.in`, inputCases[caseName].in);
      cases.file(`${caseName}.out`, inputCases[caseName].out);
      testplan += `${caseName} ${inputCases[caseName].weight || 1}\n`;
    }
    zip.file('testplan', testplan);

    const customValidator = store.getters['customValidator'];
    const settingsJson: Partial<types.ProblemSettings> = {
      Cases: store.getters['settingsCases'],
      Limits: store.getters['limits'],
      Validator: {
        Name: store.getters['Validator'],
        Tolerance: store.getters['Tolerance'] || 0,
        // Lang only appears if language exists
        ...(customValidator?.language
          ? { Lang: customValidator?.language }
          : {}),
      },
    };
    zip.file('settings.json', JSON.stringify(settingsJson, null, '  '));

    const interactive: undefined | types.InteractiveSettingsDistrib =
      store.getters['Interactive'];
    if (interactive) {
      const interactiveFolder = zip.folder('interactive');
      if (!interactiveFolder) {
        console.error('could not create interactive folder');
        return;
      }

      interactiveFolder.file(`${interactive.module_name}.idl`, interactive.idl);
      interactiveFolder.file(
        `Main.${Util.supportedLanguages[interactive.language].extension}`,
        interactive.main_source,
      );
      interactiveFolder.file('examples/sample.in', inputCases.sample?.in || '');
    }

    if (customValidator) {
      zip.file(
        `validator.${
          Util.supportedLanguages[customValidator.language].extension
        }`,
        customValidator.source,
      );
    }

    zip
      .generateAsync({ type: 'blob' })
      .then((blob) => {
        this.zipDownloadLink.download = `${store.getters['moduleName']}.zip`;
        this.zipDownloadLink.href = window.URL.createObjectURL(blob);

        store.dispatch('isDirty', false);
      })
      .catch(Util.asyncError);
  }
  RegisterVueComponent(componentName: string, component: VueComponent) {
    // eslint-disable-next-line @typescript-eslint/no-this-alias
    const self = this;

    this.goldenLayout?.registerComponent(
      componentName,
      // cannot use an arrow function because
      // it causes a "ComponentConstructor is not a constructor" error
      function (
        container: GoldenLayout.Container,
        componentState: ComponentState,
      ) {
        container.on('open', () => {
          const props: ComponentProps = {
            storeMapping: componentState.storeMapping,
            theme: self.theme,
          };
          for (const k in componentState) {
            if (k === 'id' || !componentState[k]) continue;
            props[k] = componentState[k];
          }

          const vue = new Vue({
            el: container.getElement()[0],
            components: {
              [componentName]: component,
            },
            render: function (createElement: CreateElement) {
              return createElement(componentName, {
                props: props,
              });
            },
          });

          const vueComponent: GraderComponent = vue.$children[0];
          if (vueComponent.title) {
            container.setTitle(vueComponent.title);
            vueComponent.$watch('title', function (title: string) {
              container.setTitle(title);
            });
          }
          if (vueComponent.onResize) {
            container.on('resize', () => vueComponent.onResize?.());
          }
        });

        self.componentMapping[componentState.id] = component;
      },
    );
  }

  beforeMount() {
    if (this.isEmbedded) {
      this.initProblem();
    } else {
      store.dispatch('reset');
    }
  }
  onResized() {
    if (!this.layoutRoot.clientWidth) return;
    if (!this.goldenLayout?.isInitialised) {
      this.goldenLayout?.init();
    }
    this.goldenLayout?.updateSize();
  }
  mounted() {
    this.goldenLayout = new GoldenLayout(
      this.isEmbedded ? EMBEDDED_CONFIG : UNEMBEDDED_CONFIG,
      this.layoutRoot,
    );

    this.RegisterVueComponent(CASE_SELECTOR_COMPONENT_NAME, CaseSelector);
    this.RegisterVueComponent(MONACO_EDITOR_COMPONENT_NAME, MonacoEditor);
    this.RegisterVueComponent(MONACO_DIFF_COMPONENT_NAME, DiffEditor);
    this.RegisterVueComponent(SETTINGS_COMPONENT_NAME, IDESettings);
    this.RegisterVueComponent(TEXT_EDITOR_COMPONENT_NAME, TextEditor);
    this.RegisterVueComponent(ZIP_VIEWER_COMPONENT_NAME, ZipViewer);

    this.goldenLayout.init();

    if (window.ResizeObserver) {
      new ResizeObserver(this.onResized).observe(this.layoutRoot);
    } else {
      window.addEventListener('resize', this.onResized);
    }
  }
}
</script>

<style scoped>
div > section {
  min-height: 70em;
}
@import url('https://golden-layout.com/assets/css/goldenlayout-base.css');
@import url('https://golden-layout.com/assets/css/goldenlayout-light-theme.css');
</style>
