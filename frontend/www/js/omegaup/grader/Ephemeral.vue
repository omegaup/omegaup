<template>
  <div class="ephemeral-grader" :class="theme">
    <!-- Top Navigation Bar -->
    <div class="top-navbar">
      <div class="navbar-left">
        <div class="brand">
          <svg
            width="20"
            height="20"
            viewBox="0 0 20 20"
            fill="currentColor"
            class="brand-icon"
          >
            <path
              d="M10 0C4.48 0 0 4.48 0 10s4.48 10 10 10 10-4.48 10-10S15.52 0 10 0zm-2 14.5v-9l6 4.5-6 4.5z"
            />
          </svg>
          <span class="brand-name">omegaUp</span>
          <span class="brand-subtitle">grader</span>
        </div>

        <div class="navbar-divider"></div>

        <select
          v-model="selectedLanguage"
          class="language-select"
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
      </div>

      <div class="navbar-right">
        <template v-if="!isEmbedded">
          <button class="icon-btn" :title="T.wordsUpload">
            <label class="icon-btn-label">
              <svg
                width="16"
                height="16"
                viewBox="0 0 16 16"
                fill="currentColor"
              >
                <path
                  d="M8 0l-4 4h2.5v5h3V4H12L8 0zM1 12v3h14v-3h-2v1H3v-1H1z"
                />
              </svg>
              <input
                type="file"
                accept=".zip"
                class="hidden-input"
                @change="handleUpload"
              />
            </label>
          </button>

          <a
            class="icon-btn"
            role="button"
            :href="zipHref"
            :download="zipDownload"
            :title="isDirty ? T.zipPrepare : T.wordsDownload"
            data-zip-download
            @click="handleDownload"
          >
            <svg
              v-if="isDirty"
              width="16"
              height="16"
              viewBox="0 0 16 16"
              fill="currentColor"
            >
              <path
                d="M14 0H6L4 2H2C.9 2 0 2.9 0 4v10c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V2c0-1.1-.9-2-2-2zm-4 4.5v2h-4v-2h4zm0 3v2h-4v-2h4zm-4 3h4v2h-4v-2z"
              />
            </svg>
            <svg
              v-else
              width="16"
              height="16"
              viewBox="0 0 16 16"
              fill="currentColor"
            >
              <path d="M8 12l-4-4h2.5V3h3v5H12L8 12zM1 13v2h14v-2H1z" />
            </svg>
          </a>

          <button
            class="icon-btn"
            :title="isDark ? 'Light mode' : 'Dark mode'"
            @click.prevent="toggleTheme"
          >
            <svg
              v-if="isDark"
              width="16"
              height="16"
              viewBox="0 0 16 16"
              fill="currentColor"
            >
              <circle cx="8" cy="8" r="3" />
              <path
                d="M8 0v2M8 14v2M16 8h-2M2 8H0M13.657 2.343l-1.414 1.414M3.757 12.243l-1.414 1.414M13.657 13.657l-1.414-1.414M3.757 3.757L2.343 2.343"
              />
            </svg>
            <svg
              v-else
              width="16"
              height="16"
              viewBox="0 0 16 16"
              fill="currentColor"
            >
              <path
                d="M6 0C2.686 0 0 2.686 0 6c0 3.313 2.686 6 6 6 .537 0 1.058-.072 1.552-.207C5.54 10.727 4.5 8.976 4.5 7c0-2.761 2.239-5 5-5 .34 0 .671.034.99.099A5.973 5.973 0 006 0z"
              />
            </svg>
          </button>
        </template>
      </div>
    </div>

    <!-- Main content: horizontal split -->
    <div class="main-content">
      <!-- Left: Code editor + bottom panels -->
      <div class="left-pane" :style="{ flexBasis: leftPaneWidth + '%' }">
        <!-- Code editor -->
        <div
          class="code-editor-section"
          :style="{ height: editorHeight + 'px' }"
        >
          <grader-monaco-editor
            ref="monacoEditor"
            :store-mapping="{
              contents: 'request.source',
              language: 'request.language',
              module: 'moduleName',
            }"
          />
        </div>

        <!-- Horizontal resize handle -->
        <div
          class="resize-handle resize-handle--horizontal"
          @mousedown="startEditorResize"
        >
          <div class="resize-handle-bar"></div>
        </div>

        <!-- Bottom tabs: Compiler, Logs, Files -->
        <div class="bottom-panel">
          <div class="tab-bar">
            <button
              v-for="tab in bottomTabs"
              :key="tab.id"
              class="tab-button"
              :class="{ 'tab-button--active': activeBottomTab === tab.id }"
              @click="activeBottomTab = tab.id"
            >
              {{ tab.label }}
              <span
                v-if="tab.id === 'compiler' && compilerOutput"
                class="tab-dot"
              ></span>
            </button>
          </div>
          <div class="tab-content">
            <grader-text-editor
              v-show="activeBottomTab === 'compiler'"
              :store-mapping="{ contents: 'compilerOutput' }"
              :read-only="true"
              :show-header="false"
              extension="out/err"
              module="compiler"
            />
            <grader-text-editor
              v-show="activeBottomTab === 'logs'"
              :store-mapping="{ contents: 'logs' }"
              :read-only="true"
              :show-header="false"
              extension="txt"
              module="logs"
            />
            <grader-zip-viewer
              v-show="activeBottomTab === 'files'"
              ref="zipviewer"
            />
            <grader-settings v-show="activeBottomTab === 'settings'" />
          </div>
        </div>
      </div>

      <!-- Vertical resize handle -->
      <div
        class="resize-handle resize-handle--vertical"
        @mousedown="startPaneResize"
      >
        <div class="resize-handle-bar"></div>
      </div>

      <!-- Right: Cases + Input/Output -->
      <div class="right-pane" :style="{ flexBasis: 100 - leftPaneWidth + '%' }">
        <!-- Case selector -->
        <grader-case-selector ref="caseSelector" class="case-selector-panel" />

        <!-- Input/Output section -->
        <div class="io-section">
          <div class="tab-bar">
            <button
              v-for="tab in ioTabs"
              :key="tab.id"
              class="tab-button"
              :class="{ 'tab-button--active': activeIoTab === tab.id }"
              @click="activeIoTab = tab.id"
            >
              {{ tab.label }}
            </button>
          </div>
          <div class="tab-content">
            <grader-text-editor
              v-show="activeIoTab === 'input'"
              :store-mapping="{ contents: 'inputIn', module: 'currentCase' }"
              :read-only="false"
              :show-header="false"
              extension="in"
              module="in"
            />
            <grader-text-editor
              v-show="activeIoTab === 'expected'"
              :store-mapping="{ contents: 'inputOut', module: 'currentCase' }"
              :read-only="false"
              :show-header="false"
              extension="out"
              module="out"
            />
            <grader-text-editor
              v-show="activeIoTab === 'stdout'"
              :store-mapping="{
                contents: 'outputStdout',
                module: 'currentCase',
              }"
              :read-only="true"
              :show-header="false"
              extension="out"
              module="stdout"
            />
            <grader-text-editor
              v-show="activeIoTab === 'stderr'"
              :store-mapping="{
                contents: 'outputStderr',
                module: 'currentCase',
              }"
              :read-only="true"
              :show-header="false"
              extension="err"
              module="stderr"
            />
            <grader-diff-editor
              v-show="activeIoTab === 'diff'"
              :store-mapping="{
                originalContents: 'inputOut',
                modifiedContents: 'outputStdout',
              }"
              :read-only="true"
            />
          </div>
        </div>
      </div>
    </div>

    <!-- Bottom Action Bar -->
    <div class="bottom-action-bar">
      <div class="action-bar-left">
        <!-- Compact result summary -->
        <div v-if="hasResults" class="result-pill" :class="resultSummaryClass">
          <span class="result-pill__verdict">{{ resultVerdict }}</span>
          <span class="result-pill__stats">
            {{ resultScore }}
            <template v-if="resultTime"> · {{ resultTime }}</template>
            <template v-if="resultMemory"> · {{ resultMemory }}</template>
          </span>
        </div>

        <button
          v-if="compilerOutput"
          class="compiler-alert"
          @click="activeBottomTab = 'compiler'"
        >
          <svg width="14" height="14" viewBox="0 0 14 14" fill="currentColor">
            <path d="M7 0L0 12h14L7 0zm0 3l4 7H3l4-7z" />
            <path d="M6 7h2v2H6V7zm0 3h2v2H6v-2z" />
          </svg>
          <span>Compiler output</span>
        </button>
      </div>

      <div class="action-bar-right">
        <button
          v-if="isRunButton"
          class="action-button run-button"
          :class="{ 'action-button--disabled': !canExecute }"
          :disabled="!canExecute"
          title="Run Code (Ctrl+')"
          data-run-button
          @click.prevent="handleRun"
        >
          <omegaup-countdown
            v-if="!canExecute"
            :target-time="nextExecutionTimestamp"
            :countdown-format="omegaup.CountdownFormat.EventCountdown"
            @finish="now = Date.now()"
          />
          <template v-else>
            <svg
              width="14"
              height="14"
              viewBox="0 0 14 14"
              fill="currentColor"
              class="button-icon"
            >
              <path d="M2 1v12l10-6L2 1z" />
            </svg>
            <span>Run</span>
            <span v-if="isRunLoading" class="spinner"></span>
          </template>
        </button>

        <button
          v-if="true"
          class="action-button submit-button"
          :class="{ 'action-button--disabled': !canSubmit }"
          :disabled="!canSubmit"
          title="Submit (Ctrl+Enter)"
          data-submit-button
          @click.prevent="handleSubmit"
        >
          <omegaup-countdown
            v-if="!canSubmit"
            :target-time="nextSubmissionTimestamp"
            :countdown-format="omegaup.CountdownFormat.EventCountdown"
            @finish="now = Date.now()"
          />
          <template v-else>
            <svg
              width="14"
              height="14"
              viewBox="0 0 14 14"
              fill="currentColor"
              class="button-icon"
            >
              <path d="M7 0L3 4h3v6h2V4h3L7 0zM0 12v2h14v-2H0z" />
            </svg>
            <span>Submit</span>
            <span v-if="isSubmitLoading" class="spinner"></span>
          </template>
        </button>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import * as monaco from 'monaco-editor';
(window as any).monaco = monaco;
import { Component, Prop, Watch } from 'vue-property-decorator';
import { omegaup } from '../omegaup';
import Vue from 'vue';
import omegaup_Countdown from '../components/Countdown.vue';
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
import * as ui from '../ui';

import T from '../lang';

@Component({
  components: {
    'omegaup-countdown': omegaup_Countdown,
    'grader-monaco-editor': MonacoEditor,
    'grader-text-editor': TextEditor,
    'grader-case-selector': CaseSelector,
    'grader-diff-editor': DiffEditor,
    'grader-zip-viewer': ZipViewer,
    'grader-settings': IDESettings,
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

  T = T;
  omegaup = omegaup;
  isRunLoading = false;
  isSubmitLoading = false;
  zipHref: string | null = null;
  zipDownload: string | null = null;
  now: number = Date.now();

  // Layout state
  leftPaneWidth: number = 65;
  editorHeight: number = 500;
  activeBottomTab: string = 'compiler';
  activeIoTab: string = 'input';

  // Resize state
  _isResizingPane = false;
  _isResizingEditor = false;
  _resizeStartX = 0;
  _resizeStartY = 0;
  _resizeStartValue = 0;

  get bottomTabs() {
    return [
      { id: 'compiler', label: 'Compiler' },
      { id: 'logs', label: 'Logs' },
      { id: 'files', label: 'Files' },
      { id: 'settings', label: 'Settings' },
    ];
  }

  get ioTabs() {
    return [
      { id: 'input', label: 'Input' },
      { id: 'expected', label: 'Expected' },
      { id: 'stdout', label: 'Stdout' },
      { id: 'stderr', label: 'Stderr' },
      { id: 'diff', label: 'Diff' },
    ];
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
    if (!this.hasResults || this.results.verdict !== 'AC') {
      return false;
    }
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

  get results(): GraderResults {
    return store.state.results;
  }
  get hasResults(): boolean {
    return !!this.results && !!this.results.verdict;
  }
  get resultVerdict(): string {
    if (!this.hasResults) return '';
    const verdictMap: { [key: string]: string } = {
      AC: 'Accepted',
      PA: 'Partially Accepted',
      WA: 'Wrong Answer',
      TLE: 'Time Limit Exceeded',
      MLE: 'Memory Limit Exceeded',
      OLE: 'Output Limit Exceeded',
      RTE: 'Runtime Error',
      RFE: 'Restricted Function',
      CE: 'Compilation Error',
      JE: 'Judge Error',
      VE: 'Validator Error',
    };
    return verdictMap[this.results.verdict] || this.results.verdict;
  }
  get resultScore(): string {
    if (!this.hasResults) return '';
    const score = this.results.contest_score ?? this.results.score;
    const maxScore = this.results.max_score ?? 0;
    return `${this.formatNumber(score)} / ${this.formatNumber(maxScore)}`;
  }
  get resultTime(): string | null {
    if (!this.hasResults || this.results.time == null) return null;
    if (this.results.time < 1) {
      return `${(this.results.time * 1000).toFixed(0)} ms`;
    }
    return `${this.results.time.toFixed(2)} s`;
  }
  get resultMemory(): string | null {
    if (!this.hasResults || this.results.memory == null) return null;
    if (this.results.memory > 1024 * 1024) {
      return `${(this.results.memory / (1024 * 1024)).toFixed(1)} MB`;
    }
    if (this.results.memory > 1024) {
      return `${(this.results.memory / 1024).toFixed(1)} KB`;
    }
    return `${this.results.memory} B`;
  }
  get resultSummaryClass(): string {
    if (!this.hasResults) return '';
    if (this.results.verdict === 'AC') return 'result-pill--accepted';
    if (this.results.verdict === 'PA') return 'result-pill--partial';
    return 'result-pill--error';
  }
  get compilerOutput(): string {
    return store.getters['compilerOutput'];
  }
  formatNumber(value: number): string {
    const str = value.toFixed(2);
    if (str.endsWith('.00')) return str.substring(0, str.length - 3);
    return str;
  }

  toggleTheme() {
    store.dispatch(
      'theme',
      this.theme === Util.MonacoThemes.VSLight
        ? Util.MonacoThemes.VSDark
        : Util.MonacoThemes.VSLight,
    );
  }

  // --- Resize handlers ---
  startPaneResize(e: MouseEvent) {
    e.preventDefault();
    this._isResizingPane = true;
    this._resizeStartX = e.clientX;
    this._resizeStartValue = this.leftPaneWidth;
    document.addEventListener('mousemove', this.onPaneResize);
    document.addEventListener('mouseup', this.stopPaneResize);
    document.body.style.cursor = 'col-resize';
    document.body.style.userSelect = 'none';
  }
  onPaneResize(e: MouseEvent) {
    if (!this._isResizingPane) return;
    const container = this.$el as HTMLElement;
    const dx = e.clientX - this._resizeStartX;
    const pct = (dx / container.clientWidth) * 100;
    this.leftPaneWidth = Math.min(
      80,
      Math.max(20, this._resizeStartValue + pct),
    );
  }
  stopPaneResize() {
    this._isResizingPane = false;
    document.removeEventListener('mousemove', this.onPaneResize);
    document.removeEventListener('mouseup', this.stopPaneResize);
    document.body.style.cursor = '';
    document.body.style.userSelect = '';
    this.triggerEditorResize();
  }

  startEditorResize(e: MouseEvent) {
    e.preventDefault();
    this._isResizingEditor = true;
    this._resizeStartY = e.clientY;
    this._resizeStartValue = this.editorHeight;
    document.addEventListener('mousemove', this.onEditorResize);
    document.addEventListener('mouseup', this.stopEditorResize);
    document.body.style.cursor = 'row-resize';
    document.body.style.userSelect = 'none';
  }
  onEditorResize(e: MouseEvent) {
    if (!this._isResizingEditor) return;
    const dy = e.clientY - this._resizeStartY;
    this.editorHeight = Math.min(
      800,
      Math.max(100, this._resizeStartValue + dy),
    );
  }
  stopEditorResize() {
    this._isResizingEditor = false;
    document.removeEventListener('mousemove', this.onEditorResize);
    document.removeEventListener('mouseup', this.stopEditorResize);
    document.body.style.cursor = '';
    document.body.style.userSelect = '';
    this.triggerEditorResize();
  }

  triggerEditorResize() {
    // Notify Monaco editors to re-layout
    this.$nextTick(() => {
      // Use Vue's $refs with correct type assertion for MonacoEditor
      const monacoRef = this.$refs.monacoEditor as
        | InstanceType<typeof import('./MonacoEditor.vue').default>
        | undefined;
      if (monacoRef && typeof monacoRef.onResize === 'function')
        monacoRef.onResize();
    });
  }

  initProblem() {
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
      })
      .catch((err: Error) => {
        ui.error(`Failed to initialize problem: ${err.message}`);
      });
  }
  @Watch('problem')
  @Watch('initialLanguage')
  onProblemChange() {
    this.initProblem();
  }
  @Watch('isDirty')
  onDirtyChange(value: boolean) {
    if (!value || this.isEmbedded) return;
    this.zipHref = null;
    this.zipDownload = null;
  }

  onDetailsJsonReady(results: GraderResults) {
    store.dispatch('results', results);
    store.dispatch('compilerOutput', results.compile_error || '');

    const score = results.contest_score ?? results.score ?? 0;
    const maxScore = results.max_score ?? 0;
    const scoreStr = `${this.formatNumber(score)}/${this.formatNumber(
      maxScore,
    )}`;

    switch (results.verdict) {
      case 'AC':
        ui.success(`${T.verdictAccepted || 'Accepted'} — ${scoreStr}`);
        break;
      case 'PA':
        if (typeof ui.warning === 'function') {
          ui.warning(`Partially Accepted — ${scoreStr}`);
        } else {
          ui.info(`Partially Accepted — ${scoreStr}`);
        }
        this.activeIoTab = 'diff';
        break;
      case 'CE':
        ui.error(T.verdictCompileError || 'Compilation Error');
        this.activeBottomTab = 'compiler';
        break;
      case 'JE':
        ui.error(T.verdictJudgeError || 'Judge Error');
        break;
      case 'WA':
        ui.error(`Wrong Answer — ${scoreStr}`);
        this.activeIoTab = 'diff';
        break;
      case 'TLE':
        ui.error(`Time Limit Exceeded — ${scoreStr}`);
        break;
      case 'MLE':
        ui.error(`Memory Limit Exceeded — ${scoreStr}`);
        break;
      case 'RTE':
        ui.error(`Runtime Error — ${scoreStr}`);
        break;
      case 'OLE':
        ui.error(`Output Limit Exceeded — ${scoreStr}`);
        break;
      case 'RFE':
        ui.error(`Restricted Function — ${scoreStr}`);
        break;
      default:
        if (results.verdict) {
          ui.error(`${results.verdict} — ${scoreStr}`);
        }
    }
  }
  onFilesZipReady(blob: Blob | null) {
    const zipViewer = this.$refs.zipviewer as ZipViewer | undefined;
    if (blob == null || blob.size == 0) {
      if (zipViewer) {
        zipViewer.zip = null;
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
          if (zipViewer) {
            zipViewer.zip = zip;
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
            .catch((err: Error) => {
              ui.error(`Error reading compiler output: ${err.message}`);
            });

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
        .catch((err: Error) => {
          ui.error(`Error processing zip file: ${err.message}`);
        });
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
        if (!response.ok) {
          ui.error(`Run failed with status ${response.status}`);
          return null;
        }
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
      .catch((err: Error) => {
        ui.error(`Run error: ${err.message}`);
      })
      .finally(() => {
        this.isRunLoading = false;
      });
  }
  handleDownload(e: Event) {
    if (!this.isDirty) return true;

    e.preventDefault();
    const zip = new JSZip();
    const cases = zip.folder('cases');
    if (!cases) {
      ui.error('Could not create cases folder');
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
        ui.error('Could not create interactive folder');
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
        ui.success(T.wordsDownload || 'Download ready');
      })
      .catch((err: Error) => {
        ui.error(`Download error: ${err.message}`);
      });
  }
  handleUpload(e: Event) {
    const files = (e.target as HTMLInputElement)?.files;
    if (!files || files.length !== 1) return;

    const reader = new FileReader();
    reader.addEventListener('loadend', async (e) => {
      if (e.target?.readyState != FileReader.DONE) return;

      JSZip.loadAsync(reader.result as ArrayBuffer)
        .then(async (zip) => {
          await store.dispatch('reset');
          await store.dispatch('removeCase', 'long');

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
              const settings: Partial<types.ProblemSettings> = JSON.parse(
                value,
              );
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

          ui.success(T.wordsUpload || 'Upload successful');
        })
        .catch((err: Error) => {
          ui.error(`Upload error: ${err.message}`);
        });
    });
    reader.readAsArrayBuffer(files[0]);
  }

  beforeMount() {
    this.initProblem();
  }
  mounted() {
    document.addEventListener('keydown', this.handleKeyboardShortcut);
    if (window.ResizeObserver) {
      new ResizeObserver(() => this.triggerEditorResize()).observe(this.$el);
    }
  }
  beforeDestroy() {
    document.removeEventListener('keydown', this.handleKeyboardShortcut);
  }
  handleKeyboardShortcut(e: KeyboardEvent) {
    if (
      e.ctrlKey &&
      e.key === 'Enter' &&
      this.isSubmitButton &&
      this.canSubmit
    ) {
      e.preventDefault();
      this.handleSubmit();
      return;
    }
    if (e.ctrlKey && e.key === "'" && this.isRunButton && this.canExecute) {
      e.preventDefault();
      this.handleRun();
    }
  }
}
</script>

<style lang="scss" scoped>
.ephemeral-grader {
  display: flex;
  flex-direction: column;
  max-height: 120vh;
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto,
    'Helvetica Neue', Arial, sans-serif;
  background: #fafafa;
  color: #1a1a1a;
  border: 1px solid #d1d5db;
  border-radius: 8px;
  overflow: hidden;
  margin-top: 10px;

  &.vs-dark {
    background: #1e1e1e;
    color: #d4d4d4;
    border-color: #404040;
  }
}

/* ========== Top Navigation Bar ========== */
.top-navbar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  height: 40px;
  padding: 0 12px;
  background: #fff;
  border-bottom: 1px solid #e5e7eb;
  flex-shrink: 0;
  z-index: 100;

  .vs-dark & {
    background: #252525;
    border-bottom-color: #333;
  }
}

.navbar-left,
.navbar-right {
  display: flex;
  align-items: center;
  gap: 10px;
}

.brand {
  display: flex;
  align-items: center;
  gap: 6px;
  user-select: none;
}

.brand-icon {
  color: #3b82f6;
}

.brand-name {
  font-size: 15px;
  font-weight: 700;
  color: #1a1a1a;
  .vs-dark & {
    color: #e5e5e5;
  }
}

.brand-subtitle {
  font-size: 13px;
  font-weight: 400;
  color: #6b7280;
  .vs-dark & {
    color: #9ca3af;
  }
}

.navbar-divider {
  width: 1px;
  height: 20px;
  background: #e5e7eb;
  .vs-dark & {
    background: #404040;
  }
}

.language-select {
  appearance: none;
  -webkit-appearance: none;
  padding: 5px 28px 5px 10px;
  border: 1px solid #d1d5db;
  border-radius: 6px;
  background: #fff
    url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='6' viewBox='0 0 10 6'%3E%3Cpath fill='%234b5563' d='M0 0l5 6 5-6z'/%3E%3C/svg%3E")
    no-repeat right 8px center;
  font-size: 13px;
  font-weight: 500;
  color: #1a1a1a;
  cursor: pointer;
  outline: none;
  transition: border-color 0.15s;

  &:hover {
    border-color: #9ca3af;
  }

  &:focus {
    border-color: #3b82f6;
    box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.1);
  }

  .vs-dark & {
    background-color: #2a2a2a;
    border-color: #404040;
    color: #d4d4d4;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='6' viewBox='0 0 10 6'%3E%3Cpath fill='%239ca3af' d='M0 0l5 6 5-6z'/%3E%3C/svg%3E");
    &:focus {
      border-color: #3b82f6;
    }
  }
}

.icon-btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 32px;
  height: 32px;
  border: none;
  border-radius: 6px;
  background: transparent;
  color: #6b7280;
  cursor: pointer;
  transition: all 0.15s;
  text-decoration: none;

  &:hover {
    background: #f3f4f6;
    color: #1a1a1a;
  }

  .vs-dark & {
    color: #9ca3af;
    &:hover {
      background: rgba(255, 255, 255, 0.05);
      color: #d4d4d4;
    }
  }
}

.icon-btn-label {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 100%;
  height: 100%;
  cursor: pointer;
  margin: 0;
}

.hidden-input {
  display: none;
}

/* ========== Result Pill (compact, in action bar) ========== */
.result-pill {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 5px 12px;
  border-radius: 6px;
  font-size: 12px;
  line-height: 1.4;
  animation: pill-in 0.25s ease;
}

.result-pill--accepted {
  background: rgba(16, 185, 129, 0.1);
  color: #047857;
  .vs-dark & {
    background: rgba(52, 211, 153, 0.12);
    color: #34d399;
  }
}

.result-pill--partial {
  background: rgba(245, 158, 11, 0.1);
  color: #b45309;
  .vs-dark & {
    background: rgba(251, 191, 36, 0.12);
    color: #fbbf24;
  }
}

.result-pill--error {
  background: rgba(239, 68, 68, 0.08);
  color: #dc2626;
  .vs-dark & {
    background: rgba(248, 113, 113, 0.1);
    color: #f87171;
  }
}

.result-pill__verdict {
  font-weight: 700;
}

.result-pill__stats {
  font-weight: 500;
  opacity: 0.8;
}

@keyframes pill-in {
  from {
    opacity: 0;
    transform: translateX(-6px);
  }
  to {
    opacity: 1;
    transform: translateX(0);
  }
}

/* ========== Main Content Split Layout ========== */
.main-content {
  display: flex;
  flex: 1;
  min-height: 0;
  overflow: visible;
}

.left-pane {
  display: flex;
  flex-direction: column;
  min-width: 0;
  overflow: hidden;
}

.right-pane {
  display: flex;
  flex-direction: column;
  min-width: 0;
  overflow: hidden;
}

.code-editor-section {
  min-height: 500px;
  overflow: hidden;
}

.bottom-panel {
  flex: 0 0 160px;
  display: flex;
  flex-direction: column;
  min-height: 0;
  overflow: hidden;
  border-top: 1px solid #e5e7eb;
  .vs-dark & {
    border-top-color: #333;
  }
}

.case-selector-panel {
  flex-shrink: 0;
  max-height: 55%;
  overflow: auto;
  border-bottom: 1px solid #e5e7eb;
  .vs-dark & {
    border-bottom-color: #333;
  }
}

.io-section {
  flex: 1;
  display: flex;
  flex-direction: column;
  min-height: 0;
  overflow: hidden;
}

/* ========== Resize Handles ========== */
.resize-handle {
  flex-shrink: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 10;
  transition: background 0.15s;

  &:hover {
    background: rgba(59, 130, 246, 0.08);
    .resize-handle-bar {
      background: #3b82f6;
    }
  }

  .vs-dark &:hover {
    background: rgba(59, 130, 246, 0.12);
  }
}

.resize-handle--vertical {
  width: 5px;
  cursor: col-resize;

  .resize-handle-bar {
    width: 2px;
    height: 32px;
    border-radius: 1px;
    background: #d1d5db;
    transition: background 0.15s;
    .vs-dark & {
      background: #404040;
    }
  }
}

.resize-handle--horizontal {
  height: 5px;
  cursor: row-resize;

  .resize-handle-bar {
    height: 2px;
    width: 32px;
    border-radius: 1px;
    background: #d1d5db;
    transition: background 0.15s;
    .vs-dark & {
      background: #404040;
    }
  }
}

/* ========== Tab Bar ========== */
.tab-bar {
  display: flex;
  align-items: center;
  gap: 0;
  padding: 0 8px;
  height: 32px;
  min-height: 32px;
  background: #f9fafb;
  border-bottom: 1px solid #e5e7eb;
  overflow-x: auto;
  flex-shrink: 0;

  .vs-dark & {
    background: #252525;
    border-bottom-color: #333;
  }
}

.tab-button {
  position: relative;
  display: inline-flex;
  align-items: center;
  gap: 4px;
  padding: 0 10px;
  height: 100%;
  border: none;
  background: transparent;
  color: #6b7280;
  font-size: 11px;
  font-weight: 500;
  cursor: pointer;
  white-space: nowrap;
  transition: color 0.15s;

  &:hover {
    color: #1a1a1a;
    .vs-dark & {
      color: #d4d4d4;
    }
  }

  &.tab-button--active {
    color: #3b82f6;
    font-weight: 600;

    &::after {
      content: '';
      position: absolute;
      bottom: 0;
      left: 8px;
      right: 8px;
      height: 2px;
      background: #3b82f6;
      border-radius: 1px 1px 0 0;
    }
  }

  .vs-dark &.tab-button--active {
    color: #60a5fa;
    &::after {
      background: #60a5fa;
    }
  }
}

.tab-dot {
  display: inline-block;
  width: 6px;
  height: 6px;
  border-radius: 50%;
  background: #f59e0b;
  margin-left: 2px;
}

.tab-content {
  flex: 1;
  min-height: 0;
  overflow: hidden;
  position: relative;

  > * {
    height: 100%;
  }
}

/* ========== Bottom Action Bar ========== */
.bottom-action-bar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 6px 12px;
  background: #fff;
  border-top: 1px solid #e5e7eb;
  min-height: 44px;
  flex-shrink: 0;
  z-index: 100;

  .vs-dark & {
    background: #252525;
    border-top-color: #333;
  }
}

.action-bar-left,
.action-bar-right {
  display: flex;
  align-items: center;
  gap: 10px;
}

.compiler-alert {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 5px 10px;
  border: none;
  border-radius: 6px;
  background: rgba(245, 158, 11, 0.1);
  color: #d97706;
  font-size: 12px;
  font-weight: 500;
  cursor: pointer;
  transition: background 0.15s;

  &:hover {
    background: rgba(245, 158, 11, 0.15);
  }

  .vs-dark & {
    background: rgba(245, 158, 11, 0.15);
    color: #fbbf24;
    &:hover {
      background: rgba(245, 158, 11, 0.2);
    }
  }
}

.action-button {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 6px 14px;
  border: none;
  border-radius: 6px;
  font-size: 12px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.15s;
  white-space: nowrap;

  &.action-button--disabled {
    opacity: 0.5;
    cursor: not-allowed;
  }
}

.button-icon {
  flex-shrink: 0;
}

.run-button {
  background: #fff;
  color: #1a1a1a;
  border: 1px solid #d1d5db;
  box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04);

  &:hover:not(.action-button--disabled) {
    background: #f9fafb;
    border-color: #9ca3af;
  }

  .vs-dark & {
    background: #2a2a2a;
    border-color: #404040;
    color: #d4d4d4;
    &:hover:not(.action-button--disabled) {
      background: #333;
      border-color: #525252;
    }
  }
}

.submit-button {
  background: linear-gradient(135deg, #10b981 0%, #059669 100%);
  color: #fff;
  box-shadow: 0 1px 3px rgba(16, 185, 129, 0.25);

  &:hover:not(.action-button--disabled) {
    background: linear-gradient(135deg, #059669 0%, #047857 100%);
    box-shadow: 0 2px 6px rgba(16, 185, 129, 0.3);
  }
}

.spinner {
  display: inline-block;
  width: 14px;
  height: 14px;
  border: 2px solid rgba(255, 255, 255, 0.3);
  border-top-color: currentColor;
  border-radius: 50%;
  animation: spin 0.6s linear infinite;
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}
</style>
