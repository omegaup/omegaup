<template>
  <div class="code-tester-page">
    <div class="page-header">
      <h1 class="page-title">{{ T.codeTesterTitle }}</h1>
      <p class="page-subtitle">{{ T.codeTesterDescription }}</p>
    </div>

    <div class="code-tester-grid">
      <div class="code-editor-panel">
        <div class="panel-header">
          <h2 class="panel-title">{{ T.codeTesterCode }}</h2>
          <select v-model="selectedLanguage" class="language-selector">
            <option v-for="lang in acceptedLanguages" :key="lang" :value="lang">
              {{ languageNames[lang] || lang }}
            </option>
          </select>
        </div>
        <div class="editor-container">
          <monaco-editor :store-mapping="editorStoreMapping"></monaco-editor>
        </div>
      </div>

      <div class="right-panel">
        <div class="tabs-section">
          <div class="tabs-header">
            <button
              :class="['tab-button', { active: activeTab === 'input' }]"
              @click="activeTab = 'input'"
            >
              {{ T.codeTesterInput }}
            </button>
            <button
              :class="['tab-button', { active: activeTab === 'output' }]"
              @click="activeTab = 'output'"
            >
              {{ T.codeTesterOutput }}
            </button>
            <button
              :class="['tab-button', { active: activeTab === 'error' }]"
              @click="activeTab = 'error'"
            >
              {{ T.codeTesterCompileError }}
            </button>
          </div>
          <div class="tab-content">
            <div v-if="activeTab === 'input'" class="tab-panel">
              <textarea
                v-model="inputValue"
                class="io-textarea"
                :placeholder="T.codeTesterInputPlaceholder"
              ></textarea>
            </div>

            <div v-if="activeTab === 'output'" class="tab-panel">
              <pre class="io-output">{{ outputValue || '(no output)' }}</pre>
            </div>

            <div v-if="activeTab === 'error'" class="tab-panel">
              <pre class="io-error">{{ compilerOutput || '(no errors)' }}</pre>
            </div>
          </div>
        </div>

        <div class="metrics-section">
          <div class="metric-box">
            <div class="metric-value">{{ formatTime(resultTime) }}</div>
            <div class="metric-label">{{ T.codeTesterExecutionTime }}</div>
          </div>
          <div class="metric-box">
            <div class="metric-value">{{ formatMemory(resultMemory) }}</div>
            <div class="metric-label">{{ T.codeTesterMemoryUsage }}</div>
          </div>
        </div>

        <button class="run-button" :disabled="isRunning" @click="runTest">
          <span v-if="isRunning" class="spinner"></span>
          <span class="button-text">
            {{ isRunning ? T.codeTesterRunning : T.codeTesterRun }}
          </span>
        </button>

        <div v-if="error" class="error-alert">
          {{ error }}
        </div>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import JSZip from 'jszip';
import { types } from '../../api_types';
import T from '../../lang';
import * as ui from '../../ui';
import * as Util from '../../grader/util';
import store from '../../grader/GraderStore';
import MonacoEditor from '../../grader/MonacoEditor.vue';

@Component({
  components: {
    'monaco-editor': MonacoEditor,
  },
})
export default class CodeTesterMain extends Vue {
  @Prop() payload!: types.CodeTesterPayload;

  T = T;
  acceptedLanguages: string[] = [];
  isRunning = false;
  error: string | null = null;
  activeTab: 'input' | 'output' | 'error' = 'input';

  languageNames: Record<string, string> = {
    'c11-gcc': 'C11 (GCC)',
    'c11-clang': 'C11 (Clang)',
    'cpp11-gcc': 'C++11 (GCC)',
    'cpp11-clang': 'C++11 (Clang)',
    'cpp17-gcc': 'C++17 (GCC)',
    'cpp17-clang': 'C++17 (Clang)',
    'cpp20-gcc': 'C++20 (GCC)',
    'cpp20-clang': 'C++20 (Clang)',
    java: 'Java',
    kt: 'Kotlin',
    py2: 'Python 2',
    py3: 'Python 3',
    rb: 'Ruby',
    cs: 'C#',
    pas: 'Pascal',
    hs: 'Haskell',
    lua: 'Lua',
    go: 'Go',
    rs: 'Rust',
    js: 'JavaScript',
    kp: 'Karel (Pascal)',
    kj: 'Karel (Java)',
  };

  editorStoreMapping = {
    contents: 'request.source',
    language: 'request.language',
    module: 'moduleName',
  };

  mounted(): void {
    this.acceptedLanguages = this.payload.acceptedLanguages;
    const preferredLanguage =
      this.payload.preferredLanguage ||
      this.acceptedLanguages[0] ||
      'cpp17-gcc';

    store
      .dispatch('initProblem', {
        initialLanguage: preferredLanguage,
        initialSource: '',
        initialTheme: Util.MonacoThemes.VSLight,
        languages: this.acceptedLanguages,
        problem: Util.DUMMY_PROBLEM,
        showRunButton: false,
        showSubmitButton: false,
      })
      .then(() => {
        store.dispatch('removeCase', 'long');
        store.dispatch('currentCase', 'sample');
        store.dispatch('inputIn', '');
        store.dispatch('inputOut', '');
        store.dispatch('Validator', 'token-caseless');
      })
      .catch(ui.apiError);
  }

  get selectedLanguage(): string {
    return store.getters['request.language'];
  }

  set selectedLanguage(value: string) {
    store.dispatch('request.language', value);
  }

  get inputValue(): string {
    return store.getters['inputIn'];
  }

  set inputValue(value: string) {
    store.dispatch('inputIn', value);
  }

  get outputValue(): string {
    return store.getters['outputStdout'] || '';
  }

  get compilerOutput(): string {
    return store.getters['compilerOutput'] || '';
  }

  get resultTime(): number {
    return store.state.results?.time || 0;
  }

  get resultMemory(): number {
    return store.state.results?.memory || 0;
  }

  formatTime(value: number): string {
    if (value === 0) return '0.00 ms';
    const ms = value * 1000;
    if (ms < 1000) return `${ms.toFixed(2)} ms`;
    return `${value.toFixed(2)} s`;
  }

  formatMemory(value: number): string {
    if (value === 0) return '0.00 MB';
    return `${(value / (1024 * 1024)).toFixed(2)} MB`;
  }

  async runTest(): Promise<void> {
    if (this.isRunning) return;

    const source = store.getters['request.source'];
    if (!source || !source.trim()) {
      ui.error(T.codeTesterErrorNoCode);
      return;
    }

    this.error = null;
    this.isRunning = true;
    store.dispatch('clearOutputs');
    store.dispatch('compilerOutput', '');

    try {
      const response = await fetch('/grader/ephemeral/run/new/', {
        method: 'POST',
        headers: new Headers({
          'Content-Type': 'application/json',
        }),
        body: JSON.stringify(store.getters['request']),
      });

      if (!response.ok) {
        throw new Error(`${T.codeTesterErrorHttp}: ${response.status}`);
      }

      const formData = await response.formData();
      let results = {
        contest_score: 0,
        judged_by: 'runner',
        max_score: 0,
        score: 0,
        verdict: 'JE',
      };

      const detailsFile = formData.get('details.json') as File | null;
      if (detailsFile) {
        try {
          results = JSON.parse(await detailsFile.text());
        } catch {
          // Keep fallback results.
        }
      }

      store.dispatch('results', results);
      let compilerOutput = results.compile_error || '';
      if (compilerOutput) {
        store.dispatch('compilerOutput', compilerOutput);
      }

      const filesZip = formData.get('files.zip') as File | null;
      if (filesZip && filesZip.size > 0) {
        const zip = await JSZip.loadAsync(await filesZip.arrayBuffer());
        store.dispatch('clearOutputs');

        const compileErr = await zip.file('Main/compile.err')?.async('string');
        const compileOut = await zip.file('Main/compile.out')?.async('string');
        if (compileErr || compileOut) {
          compilerOutput = (compileErr || compileOut || '').trim();
          store.dispatch('compilerOutput', compilerOutput);
        } else if (!compilerOutput) {
          store.dispatch('compilerOutput', '');
        }

        for (const filename in zip.files) {
          if (filename.indexOf('/') !== -1) continue;
          const file = zip.file(filename);
          if (!file) continue;
          const contents = await file.async('string');
          store.dispatch('output', {
            name: filename,
            contents,
          });
        }
      }

      if (compilerOutput || results.verdict === 'CE') {
        this.activeTab = 'error';
      } else {
        this.activeTab = 'output';
      }

      if (results.verdict === 'CE') {
        ui.error(T.codeTesterCompileError);
      } else if (results.verdict === 'TLE') {
        ui.warning('Time limit exceeded');
      } else if (results.verdict === 'RTE') {
        ui.warning('Runtime error');
      } else if (results.verdict === 'JE') {
        ui.error(T.codeTesterErrorUnknown);
      } else {
        ui.success(T.codeTesterSuccessMessage);
      }
    } catch (err) {
      this.error =
        err instanceof Error ? err.message : T.codeTesterErrorUnknown;
      ui.error(this.error);
    } finally {
      this.isRunning = false;
    }
  }
}
</script>

<style scoped lang="scss">
@import '../../../../sass/main.scss';
.code-tester-page {
  padding: 1.5rem;
  padding-top: 1.5rem;
  max-width: 100%;
  background-color: var(--arena-problem-background-color);
}

.page-header {
  margin-bottom: 1.5rem;

  .page-title {
    font-size: 1.75rem;
    font-weight: 700;
    margin: 0 0 0.5rem 0;
    color: var(--btn-intro-js-font-color);
  }

  .page-subtitle {
    font-size: 1rem;
    color: var(--arena-reset-text-color);
    margin: 0;
  }
}

.code-tester-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1.5rem;

  @media (max-width: 1200px) {
    grid-template-columns: 1fr;
  }
}

/* Code Editor Panel */
.code-editor-panel {
  display: flex;
  flex-direction: column;
  background: var(--arena-problem-background-color);
  border: 1px solid var(--arena-problem-details-border-color);
  border-radius: 0.25rem;
  overflow: hidden;

  .panel-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.75rem 1rem;
    background-color: var(--arena-problem-details-header-background);
    border-bottom: 1px solid var(--arena-problem-details-border-color);
    gap: 1rem;

    .panel-title {
      font-size: 1rem;
      font-weight: 600;
      margin: 0;
      color: var(--btn-intro-js-font-color);
      flex: 1;
    }
  }

  .editor-container {
    flex: 1;
    min-height: 500px;
    overflow: hidden;
  }
}

.language-selector {
  padding: 0.375rem 0.75rem;
  font-size: 0.875rem;
  border: 1px solid var(--arena-button-border-color);
  border-radius: 0.25rem;
  background-color: var(--arena-problem-background-color);
  color: var(--btn-intro-js-font-color);
  min-width: 180px;
  cursor: pointer;

  &:focus {
    outline: none;
    border-color: var(--header-primary-color);
    box-shadow: 0 0 0 3px var(--problem-progress-border-light-color);
  }
}

/* Right Panel */
.right-panel {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

/* Tabs Section */
.tabs-section {
  background: var(--arena-problem-background-color);
  border: 1px solid var(--arena-problem-details-border-color);
  border-radius: 0.25rem;
  overflow: hidden;

  .tabs-header {
    display: flex;
    background-color: var(--arena-problem-details-header-background);
    border-bottom: 2px solid var(--arena-problem-details-border-color);

    .tab-button {
      flex: 1;
      padding: 0.75rem 1rem;
      font-size: 0.875rem;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      border: none;
      background: transparent;
      color: var(--arena-reset-text-color);
      cursor: pointer;
      transition: all 0.2s ease;
      border-bottom: 3px solid transparent;

      &:hover {
        background-color: var(--problem-progress-bg-light-color);
        color: var(--btn-intro-js-font-color);
      }

      &.active {
        color: var(--header-primary-color);
        border-bottom-color: var(--header-primary-color);
        background-color: var(--arena-problem-background-color);
      }

      &:focus {
        outline: none;
      }
    }
  }

  .tab-content {
    min-height: 200px;
    max-height: 300px;
    overflow: auto;
    padding: 0.75rem;
    background-color: var(--arena-problem-background-color);
  }

  .tab-panel {
    height: 100%;
  }
}

.io-textarea {
  width: 100%;
  min-height: 200px;
  max-height: 300px;
  padding: 0.75rem;
  font-family: 'Monaco', 'Menlo', 'Ubuntu Mono', 'Courier New', monospace;
  font-size: 0.875rem;
  line-height: 1.5;
  border: 1px solid var(--arena-problem-details-border-color);
  border-radius: 0.25rem;
  background-color: var(--arena-problem-background-color);
  color: var(--btn-intro-js-font-color);
  resize: none;

  &::placeholder {
    color: var(--arena-reset-text-color);
  }

  &:focus {
    outline: none;
  }
}

.io-output,
.io-error {
  margin: 0;
  padding: 0.75rem;
  min-height: 200px;
  max-height: 300px;
  font-family: 'Monaco', 'Menlo', 'Ubuntu Mono', 'Courier New', monospace;
  font-size: 0.875rem;
  line-height: 1.5;
  border: 1px solid var(--arena-problem-details-border-color);
  border-radius: 0.25rem;
  color: var(--btn-intro-js-font-color);
  background-color: var(--arena-problem-background-color);
  white-space: pre-wrap;
  word-wrap: break-word;
  overflow-y: auto;
}

.io-error {
  color: var(--badges-grader-error-font-color);
}

/* Metrics Section */
.metrics-section {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1rem;

  .metric-box {
    padding: 1rem;
    background-color: var(--problem-progress-bg-light-color);
    border: 1px solid var(--problem-progress-border-light-color);
    border-radius: 0.25rem;
    text-align: center;

    .metric-value {
      font-size: 1.5rem;
      font-weight: 700;
      color: var(--btn-intro-js-font-color);
      font-family: 'Monaco', 'Menlo', 'Ubuntu Mono', 'Courier New', monospace;
      margin-bottom: 0.25rem;
    }

    .metric-label {
      font-size: 0.75rem;
      font-weight: 600;
      text-transform: uppercase;
      color: var(--arena-reset-text-color);
      letter-spacing: 0.5px;
    }
  }

  @media (max-width: 768px) {
    grid-template-columns: 1fr;
  }
}

/* Run Button */
.run-button {
  width: 100%;
  padding: 0.75rem 1.5rem;
  font-size: 1rem;
  font-weight: 600;
  border: none;
  border-radius: 0.25rem;
  background-color: var(--btn-ok-background-color);
  color: var(--arena-problem-background-color);
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  transition: background-color 0.15s ease-in-out;

  &:hover:not(:disabled) {
    filter: brightness(0.92);
  }

  &:disabled {
    opacity: 0.65;
    cursor: not-allowed;
  }

  &:focus {
    outline: none;
    box-shadow: 0 0 0 0.2rem var(--problem-progress-border-light-color);
  }

  .spinner {
    width: 16px;
    height: 16px;
    border: 2px solid rgba(255, 255, 255, 0.3);
    border-top-color: var(--arena-problem-background-color);
    border-radius: 50%;
    animation: spin 0.6s linear infinite;
  }

  .button-text {
    font-size: 1rem;
  }
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}

/* Error Alert */
.error-alert {
  padding: 0.75rem 1rem;
  background-color: var(--badges-grader-error-background-color);
  border-left: 4px solid var(--badges-grader-error-font-color);
  border-radius: 0.25rem;
  color: var(--badges-grader-error-font-color);

  strong {
    display: block;
    margin-bottom: 0.5rem;
  }

  div {
    word-break: break-word;
  }
}

/* Responsive */
@media (max-width: 768px) {
  .code-tester-page {
    padding: 1rem;
  }

  .code-tester-grid {
    gap: 1rem;
  }

  .page-header {
    .page-title {
      font-size: 1.5rem;
    }
  }
}
</style>
