<template>
  <div class="d-flex flex-column h-100">
    <div class="navbar py-0" :class="theme">
      <span class="navbar-brand">
        omegaUp ephemeral grader
        <sup>&alpha;</sup>
      </span>
      <form class="form-inline my-2 my-lg-0 ephemeral-form">
        <template v-if="!isEmbedded">
          <label>
            <a class="btn btn-secondary btn-sm mr-sm-2" role="button">
              <font-awesome-icon
                :icon="['fas', 'upload']"
                :title="T.wordsUpload"
                aria-hidden="true"
                data-zip-upload
              />
            </a>
            <input
              type="file"
              accept=".zip"
              class="d-none"
              @change="handleUpload"
            />
          </label>
          <label>
            <a
              class="btn btn-secondary btn-sm mr-sm-2"
              role="button"
              :href="zipHref"
              :download="zipDownload"
              data-zip-download
              @click="handleDownload"
            >
              <font-awesome-icon
                :icon="isDirty ? ['fas', 'file-archive'] : ['fas', 'download']"
                :title="isDirty ? T.zipPrepare : T.wordsDownload"
                aria-hidden="true"
              />
            </a>
          </label>
          <label>
            <button
              class="btn btn-secondary btn-sm mr-2"
              @click.prevent="toggleTheme"
            >
              <font-awesome-icon
                :icon="isDark ? ['fas', 'sun'] : ['fas', 'moon']"
                aria-hidden="true"
              />
            </button>
          </label>
        </template>
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
          :disabled="!canExecute"
          :class="{ disabled: !canExecute }"
          class="btn btn-sm btn-secondary mr-2 my-sm-0"
          data-run-button
          @click.prevent="handleRun"
        >
          <omegaup-countdown
            v-if="!canExecute"
            :target-time="nextExecutionTimestamp"
            :countdown-format="omegaup.CountdownFormat.EventCountdown"
            @finish="now = Date.now()"
          ></omegaup-countdown>
          <template v-else>
            <span>{{ T.wordsRun }}</span>
            <span
              v-if="isRunLoading"
              class="spinner-border spinner-border-sm"
              role="status"
              aria-hidden="true"
            ></span>
          </template>
        </button>
        <button
          v-if="isSubmitButton"
          :disabled="!canSubmit"
          :class="{ disabled: !canSubmit }"
          class="btn btn-sm btn-primary my-2 my-sm-0"
          data-submit-button
          @click.prevent="handleSubmit"
        >
          <omegaup-countdown
            v-if="!canSubmit"
            :target-time="nextSubmissionTimestamp"
            :countdown-format="omegaup.CountdownFormat.EventCountdown"
            @finish="now = Date.now()"
          ></omegaup-countdown>
          <template v-else>
            <span>{{ T.wordsSubmit }}</span>
            <span
              v-if="isSubmitLoading"
              class="spinner-border spinner-border-sm"
              role="status"
              aria-hidden="true"
            ></span>
          </template>
        </button>
      </form>
    </div>
    <section ref="layout-root" class="col px-0 flex-grow-1"></section>
  </div>
</template>

<script lang="ts">
import * as monaco from 'monaco-editor';
(window as any).monaco = monaco;
import { Component, Prop, Ref, Watch } from 'vue-property-decorator';
import { omegaup } from '../omegaup';
import Vue, { CreateElement } from 'vue';
import omegaup_Countdown from '../components/Countdown.vue';
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

import { library } from '@fortawesome/fontawesome-svg-core';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import {
  faUpload,
  faFileArchive,
  faDownload,
  faSun,
  faMoon,
} from '@fortawesome/free-solid-svg-icons';
library.add(faUpload, faFileArchive, faDownload, faSun, faMoon);

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

@Component({
  components: {
    'font-awesome-icon': FontAwesomeIcon,
    'omegaup-countdown': omegaup_Countdown,
  },
})
export default class Ephemeral extends Vue {
  @Prop({ required: true }) acceptedLanguages!: string[];
  @Prop({ required: true }) isEmbedded!: boolean;
  @Prop({ required: true }) canRun!: boolean;
  @Prop({ required: true }) shouldShowSubmitButton!: boolean;
  @Prop({ required: true }) initialLanguage!: string;
  @Prop({ required: true }) initialTheme!: Util.MonacoThemes;
  @Prop({ required: true }) problem!: types.ProblemInfo;
  @Prop({ default: null }) nextSubmissionTimestamp!: null | Date;
  @Prop({ default: null }) nextExecutionTimestamp!: null | Date;

  @Ref('layout-root') readonly layoutRoot!: HTMLElement;

  readonly themeToRef: { [key: string]: string } = {
    [Util.MonacoThemes
      .VSLight]: `https://golden-layout.com/assets/css/goldenlayout-light-theme.css`,
    [Util.MonacoThemes
      .VSDark]: `https://golden-layout.com/assets/css/goldenlayout-dark-theme.css`,
  };
  goldenLayout: GoldenLayout | null = null;
  componentMapping: { [key: string]: GraderComponent } = {};
  T = T;
  omegaup = omegaup;
  isRunLoading = false;
  isSubmitLoading = false;
  zipHref: string | null = null;
  zipDownload: string | null = null;
  now: number = Date.now();

  get isSubmitButton() {
    return store.getters['showSubmitButton'];
  }
  get isRunButton() {
    return store.getters['showRunButton'];
  }
  get isDirty() {
    return store.getters['isDirty'];
  }
  get theme() {
    return store.getters['theme'];
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
  get isDark() {
    return this.theme === Util.MonacoThemes.VSDark;
  }
  get canSubmit(): boolean {
    if (!this.nextSubmissionTimestamp) {
      return true;
    }
    return this.nextSubmissionTimestamp.getTime() <= this.now;
  }
  get canExecute(): boolean {
    if (!this.nextExecutionTimestamp) {
      return true;
    }
    return this.nextExecutionTimestamp.getTime() <= this.now;
  }

  toggleTheme() {
    store.dispatch(
      'theme',
      this.theme === Util.MonacoThemes.VSLight
        ? Util.MonacoThemes.VSDark
        : Util.MonacoThemes.VSLight,
    );
  }
  initProblem() {
    // use commits for synchronous behavior
    // or else bugs occur where layout toggles cases column
    // when it shouldn't
    store.commit('updatingSettings', true);
    store
      .dispatch('initProblem', {
        initialLanguage: this.initialLanguage,
        initialTheme: this.initialTheme,
        languages: this.acceptedLanguages,
        problem: this.problem,
        showRunButton: this.canRun,
        showSubmitButton: this.shouldShowSubmitButton,
      })
      .then(() => {
        store.commit('updatingSettings', false);
        this.$nextTick(() => {
          if (!this.isEmbedded || !this.goldenLayout?.isInitialised) return;
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
    this.initProblem();
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
    this.zipHref = null;
    this.zipDownload = null;
  }
  @Watch('theme')
  onThemeChange() {
    // remove old theme
    for (const theme in this.themeToRef) {
      if (theme === this.theme) continue;
      const link = document.getElementById(this.themeToRef[theme]);
      if (link) link.remove();
    }
    this.downloadThemeStylesheet(this.theme);
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

    postMessage({
      method: 'executeRun',
      params: {
        problem_alias: store.getters['alias'],
        language: store.getters['request.language'],
        source: store.getters['request.source'],
      },
    });
    this.$emit('execute-run');

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
        this.zipDownload = `${store.getters['moduleName']}.zip`;
        this.zipHref = window.URL.createObjectURL(blob);

        store.dispatch('isDirty', false);
      })
      .catch(Util.asyncError);
  }
  handleUpload(e: Event) {
    const files = (e.target as HTMLInputElement)?.files;
    if (!files || files.length !== 1) return;

    const reader = new FileReader();
    reader.addEventListener('loadend', async (e) => {
      if (e.target?.readyState != FileReader.DONE) return;
      // due to the way files are strcutured
      // to work as intended i use async awaits instead of promises

      JSZip.loadAsync(reader.result as ArrayBuffer).then(async (zip) => {
        await store.dispatch('reset');
        await store.dispatch('removeCase', 'long');

        // testplan is only used to give weights to cases
        // we need to get weights before creating the cases
        const testplanValue = await zip.file('testplan')?.async('string');
        const casesWeights: { [key: string]: number } = {};
        if (testplanValue) {
          for (const line of testplanValue.split('\n')) {
            if (line.startsWith('#') || line.trim() === '') continue;
            const tokens = line.split(/\s+/);

            if (tokens.length !== 2) continue;
            const [caseName, weight] = tokens;
            casesWeights[caseName] = parseFloat(weight);
          }
        }

        for (const fileName in zip.files) {
          if (!zip.files[fileName]) continue;

          if (fileName.startsWith('cases/') && fileName.endsWith('.in')) {
            const caseName = fileName.substring(
              'cases/'.length,
              fileName.length - '.in'.length,
            );
            const caseInFileName = fileName;
            const caseOutFileName = `cases/${caseName}.out`;

            // both casename.in and casename.out must exist
            Promise.all([
              zip.file(caseInFileName)?.async('string'),
              zip.file(caseOutFileName)?.async('string'),
            ])
              .then(([caseIn, caseOut]) => {
                store.dispatch('createCase', {
                  name: caseName,
                  in: caseIn,
                  out: caseOut,
                  weight: casesWeights[caseName] || 1,
                });
              })
              .catch(Util.asyncError);
          } else if (fileName.startsWith('validator.')) {
            const extension = fileName.substring('validator.'.length);
            if (!Util.supportedExtensions.includes(extension)) continue;

            zip
              .file(fileName)
              ?.async('string')
              .then((value) => {
                // the validator need to be set first
                // before updating language and source
                store.dispatch('Validator', 'custom').then(() => {
                  store.dispatch(
                    'request.input.validator.custom_validator.language',
                    extension,
                  );
                  store.dispatch(
                    'request.input.validator.custom_validator.source',
                    value,
                  );
                });
              })
              .catch(Util.asyncError);
          } else if (
            fileName.startsWith('interactive/') &&
            fileName.endsWith('.idl')
          ) {
            const moduleName = fileName.substring(
              'interactive/'.length,
              fileName.length - '.idl'.length,
            );

            zip
              .file(fileName)
              ?.async('string')
              .then((value) => {
                store.dispatch('Interactive', {
                  idl: value,
                  module_name: moduleName,
                });
              })
              .catch(Util.asyncError);
          } else if (fileName.startsWith('interactive/Main.')) {
            const extension = fileName.substring('interactive/Main.'.length);
            if (!Util.supportedExtensions.includes(extension)) continue;

            zip
              .file(fileName)
              ?.async('string')
              .then((value) => {
                store.dispatch('Interactive', {
                  language: extension,
                  main_source: value,
                });
              })
              .catch(Util.asyncError);
          }
        }
        zip
          .file('settings.json')
          ?.async('string')
          .then((value) => {
            const settings: Partial<types.ProblemSettings> = JSON.parse(value);
            if (settings.Limits) {
              store.dispatch('limits', settings.Limits);
            }
            if (settings.Validator?.Name) {
              store.dispatch('Validator', settings.Validator.Name);
            }
            if (settings.Validator?.Tolerance) {
              store.dispatch('Tolerance', settings.Validator.Tolerance);
            }
          })
          .catch(Util.asyncError);
      });
    });
    reader.readAsArrayBuffer(files[0]);
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
          self.componentMapping[componentState.id] = vueComponent;
        });
      },
    );
  }

  onResized() {
    if (!this.layoutRoot.clientWidth) return;
    if (!this.goldenLayout?.isInitialised) {
      this.goldenLayout?.init();
    }
    this.goldenLayout?.updateSize();
  }
  downloadThemeStylesheet(theme: string) {
    const link = document.createElement('link');
    link.rel = 'stylesheet';
    link.href = this.themeToRef[theme];
    document.head.appendChild(link);
  }
  beforeMount() {
    this.initProblem();
    this.downloadThemeStylesheet(this.theme);
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

<style lang="scss" scoped>
@import '../../../sass/main.scss';

div > section {
  min-height: 60em;
}
div {
  &.vs-dark {
    background: var(--vs-dark-background-color);
    color: var(--vs-dark-font-color);
    border-bottom: 1px solid var(--vs-dark-background-color);

    /* Target the language selector */
    .form-control.form-control-sm[data-language-select] {
      background-color: var(--vs-dark-background-color);
      color: var(--vs-dark-font-color);
    }
  }
  &.vs {
    background: var(--vs-background-color);
    border-bottom: 1px solid var(--vs-background-color);
  }
}
a:hover {
  color: var(--zip-button-color--hover);
}
@import url('https://golden-layout.com/assets/css/goldenlayout-base.css');
</style>
