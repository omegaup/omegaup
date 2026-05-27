<template>
  <div
    :class="[
      'h-100',
      'd-flex',
      'flex-column',
      'monaco-root',
      theme,
      { 'monaco-root--fullscreen': isFullscreen },
    ]"
    role="region"
    :aria-label="ui.formatString(T.monacoEditorRegionLabel, { filename })"
  >
    <div
      class="editor-toolbar"
      role="toolbar"
      :aria-label="T.monacoEditorToolbarAriaLabel"
    >
      <span class="toolbar-filename" :title="filename">
        <i class="far fa-file-code file-icon" aria-hidden="true"></i>
        {{ filename }}
      </span>

      <div class="toolbar-right">
        <label class="toolbar-label">{{ T.fontSize }}</label>
        <select
          v-model="selectedFontSize"
          class="toolbar-select"
          :aria-label="T.monacoEditorFontSizeAriaLabel"
          @change="onFontSizeChange"
        >
          <option v-for="size in fontSizes" :key="size" :value="size">
            {{ ui.formatString(T.monacoEditorFontSizeOption, { size }) }}
          </option>
        </select>

        <button
          v-if="contents"
          v-clipboard="() => contents"
          v-clipboard:success="handleCopyFeedback"
          v-clipboard:error="handleCopyError"
          class="toolbar-btn toolbar-btn--copy"
          :class="{ 'toolbar-btn--copied': copied }"
          :title="copyButtonText"
          :aria-label="copyButtonText"
        >
          <font-awesome-icon v-if="!copied" icon="clipboard" />
          <font-awesome-icon v-else icon="check" />
          <span class="sr-only">{{ copyButtonText }}</span>
        </button>

        <button
          v-if="!readOnly && hasChanges"
          class="toolbar-btn toolbar-btn--reset"
          :title="T.monacoEditorResetTitle"
          :aria-label="T.monacoEditorResetAriaLabel"
          @click="confirmReset"
        >
          <i class="fas fa-undo" aria-hidden="true"></i>
          <span class="sr-only">{{ T.monacoEditorReset }}</span>
        </button>

        <button
          class="toolbar-btn"
          :title="
            isFullscreen
              ? T.monacoEditorExitFullscreen
              : T.monacoEditorFullscreen
          "
          :aria-label="
            isFullscreen
              ? T.monacoEditorExitFullscreenLabel
              : T.monacoEditorEnterFullscreen
          "
          :aria-pressed="isFullscreen"
          @click="toggleFullscreen"
        >
          <i v-if="!isFullscreen" class="fas fa-expand" aria-hidden="true"></i>
          <i v-else class="fas fa-compress" aria-hidden="true"></i>
        </button>
      </div>
    </div>

    <div
      ref="editorContainer"
      class="editor flex-grow-1 w-100 h-100"
      role="textbox"
      :aria-label="T.monacoEditorCodeEditorContent"
      aria-multiline="true"
    ></div>

    <div
      v-if="showResetModal"
      class="modal-overlay"
      @click.self="showResetModal = false"
    >
      <div class="modal-content" role="dialog" aria-modal="true">
        <h3>{{ T.monacoEditorResetModalTitle }}</h3>
        <p>{{ T.monacoEditorResetModalMessage }}</p>
        <div class="modal-actions">
          <button class="btn btn-secondary" @click="showResetModal = false">
            {{ T.wordsCancel }}
          </button>
          <button class="btn btn-danger" @click="resetToDefault">
            {{ T.monacoEditorReset }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop, Watch } from 'vue-property-decorator';
import store from '../grader/GraderStore';
import * as Util from '../grader/util';
import * as monaco from 'monaco-editor';
import T from '../lang';
import * as ui from '../ui';
import Clipboard from 'v-clipboard';
import { library } from '@fortawesome/fontawesome-svg-core';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { faClipboard, faCheck } from '@fortawesome/free-solid-svg-icons';
library.add(faClipboard, faCheck);
Vue.use(Clipboard);

// ─── Sourced from constants.ts ────────────────────────────────────────────────
import { EDITOR, TIMING } from '../grader/constants';
// EDITOR.FONT_SIZES           → [12, 13, 14, 16, 18, 20]
// EDITOR.DEFAULT_FONT_SIZE    → 13
// TIMING.COPY_FEEDBACK_DURATION_MS  → 2000
// TIMING.DEBOUNCE_EDITOR_CHANGE_MS  → 300

// ─── Sourced from util.ts ─────────────────────────────────────────────────────
// Util.MonacoThemes.VSLight   → 'vs'     (NOT 'vs-light')
// Util.MonacoThemes.VSDark    → 'vs-dark'
// Util.supportedLanguages     → language metadata keyed by language id

// ─── Local constants (not candidates for constants.ts – component-specific) ──
const EVENTS = {
  CODE_AND_LANGUAGE_SET: 'code-and-language-set',
} as const;

// How long (ms) to wait after the last resize before calling editor.layout().
// Kept here rather than constants.ts because it is a UI micro-timing detail
// that only matters to this component's internal resize debounce.
const FULLSCREEN_LAYOUT_DELAY_MS = 100;

// Fallback values when the Vuex store has not yet been initialised.
// These mirror the values already used by MonacoThemes / supportedLanguages so
// they are declared here for clarity rather than duplicated in constants.ts.
const FALLBACKS = {
  THEME: Util.MonacoThemes.VSLight, // 'vs'
  LANGUAGE: 'javascript',
  MODULE: T.monacoEditorUntitled,
  EXTENSION: 'js',
  MODEL_MAPPING: 'javascript',
} as const;

@Component({
  components: {
    'font-awesome-icon': FontAwesomeIcon,
  },
})
export default class MonacoEditor extends Vue {
  @Prop({ required: true }) storeMapping!: { [key: string]: string };
  @Prop({ default: false }) readOnly!: boolean;

  _editor: monaco.editor.IStandaloneCodeEditor | null = null;
  _model: monaco.editor.ITextModel | null = null;

  // ── State ──────────────────────────────────────────────────────────────────
  // Default font size and available sizes both come from constants.ts so there
  // is a single source of truth shared across the IDE.
  selectedFontSize: number = EDITOR.DEFAULT_FONT_SIZE;
  fontSizes: readonly number[] = EDITOR.FONT_SIZES;

  isFullscreen: boolean = false;
  copied: boolean = false;
  showResetModal: boolean = false;
  defaultContents: string = '';

  // ── Timers & observers ────────────────────────────────────────────────────
  private copyTimeout: number | null = null;
  private resizeObserver: ResizeObserver | null = null;
  private debounceTimer: number | null = null;

  T = T;
  ui = ui;

  // ── Computed ──────────────────────────────────────────────────────────────
  get theme(): string {
    return store.getters['theme'] || FALLBACKS.THEME;
  }

  get language(): string {
    return store.getters[this.storeMapping?.language] || FALLBACKS.LANGUAGE;
  }

  get module(): string {
    return store.getters[this.storeMapping?.module] || FALLBACKS.MODULE;
  }

  get contents(): string {
    return store.getters[this.storeMapping?.contents] || '';
  }

  set contents(value: string) {
    if (this.storeMapping?.contents) {
      store.dispatch(this.storeMapping.contents, value);
    }
  }

  get filename(): string {
    const langObj = Util.supportedLanguages[this.language];
    const extension = langObj ? langObj.extension : FALLBACKS.EXTENSION;
    return `${this.module}.${extension}`;
  }

  get copyButtonText(): string {
    return this.copied ? T.monacoEditorCopied : T.monacoEditorCopyCode;
  }

  get hasChanges(): boolean {
    return (
      this.defaultContents !== '' && this.contents !== this.defaultContents
    );
  }

  // ── Watchers ──────────────────────────────────────────────────────────────
  @Watch('language')
  onLanguageChange(value: string): void {
    if (this._model) {
      const langObj = Util.supportedLanguages[value];
      monaco.editor.setModelLanguage(
        this._model,
        langObj ? langObj.modelMapping : FALLBACKS.MODEL_MAPPING,
      );
    }
  }

  @Watch('contents')
  onContentsChange(value: string): void {
    if (this._model && this._model.getValue() !== value) {
      this._model.setValue(value);
    }
  }

  @Watch('theme')
  onThemeChange(value: string): void {
    if (this._editor) {
      this._editor.updateOptions({ theme: value });
    }
  }

  // ── Lifecycle ─────────────────────────────────────────────────────────────
  mounted(): void {
    window.addEventListener(
      EVENTS.CODE_AND_LANGUAGE_SET,
      this.onCodeAndLanguageSet,
    );
    document.addEventListener('keydown', this.handleKeydown);

    const container = this.$refs.editorContainer as HTMLElement;
    if (!container) return;

    this.defaultContents = this.contents;

    const langObj = Util.supportedLanguages[this.language];
    const modelMapping = langObj
      ? langObj.modelMapping
      : FALLBACKS.MODEL_MAPPING;

    this._editor = monaco.editor.create(container, {
      autoIndent: 'brackets',
      formatOnPaste: true,
      formatOnType: true,
      language: modelMapping,
      readOnly: this.readOnly,
      theme: this.theme,
      value: this.contents,
      fontSize: this.selectedFontSize,
      minimap: { enabled: true },
      automaticLayout: true,
    } as monaco.editor.IStandaloneEditorConstructionOptions);

    this._model = this._editor.getModel();
    if (!this._model) return;

    this._model.onDidChangeContent(() => {
      if (this.debounceTimer) clearTimeout(this.debounceTimer);
      // Delay sourced from TIMING.DEBOUNCE_EDITOR_CHANGE_MS in constants.ts
      this.debounceTimer = window.setTimeout(() => {
        this.contents = this._model?.getValue() || '';
      }, TIMING.DEBOUNCE_EDITOR_CHANGE_MS);
    });

    if (window.ResizeObserver) {
      this.resizeObserver = new ResizeObserver(() => this.onResize());
      this.resizeObserver.observe(this.$el as HTMLElement);
    } else {
      window.addEventListener('resize', this.onResize);
    }

    this.onResize();
  }

  destroyed(): void {
    if (this.copyTimeout) clearTimeout(this.copyTimeout);
    if (this.debounceTimer) clearTimeout(this.debounceTimer);

    window.removeEventListener(
      EVENTS.CODE_AND_LANGUAGE_SET,
      this.onCodeAndLanguageSet,
    );
    document.removeEventListener('keydown', this.handleKeydown);

    if (this.resizeObserver) {
      this.resizeObserver.disconnect();
    } else {
      window.removeEventListener('resize', this.onResize);
    }

    if (this.isFullscreen) document.body.style.overflow = '';

    if (this._editor) {
      this._editor.dispose();
      this._editor = null;
    }
    if (this._model) {
      this._model.dispose();
      this._model = null;
    }
  }

  // ── Methods ───────────────────────────────────────────────────────────────
  handleCopyFeedback(): void {
    this.copied = true;
    if (this.copyTimeout) clearTimeout(this.copyTimeout);
    // Duration sourced from TIMING.COPY_FEEDBACK_DURATION_MS in constants.ts
    this.copyTimeout = window.setTimeout(
      () => (this.copied = false),
      TIMING.COPY_FEEDBACK_DURATION_MS,
    );
  }

  handleCopyError(): void {
    if (this.copyTimeout) clearTimeout(this.copyTimeout);
    this.copied = false;
    ui.error(T.monacoEditorClipboardError);
  }

  confirmReset(): void {
    this.showResetModal = true;
  }

  resetToDefault(): void {
    this.contents = this.defaultContents;
    if (this._model) {
      this._model.setValue(this.defaultContents);
    }
    this.showResetModal = false;
  }

  toggleFullscreen(): void {
    this.isFullscreen = !this.isFullscreen;
    document.body.style.overflow = this.isFullscreen ? 'hidden' : '';
    setTimeout(() => this.onResize(), FULLSCREEN_LAYOUT_DELAY_MS);
  }

  handleKeydown(e: KeyboardEvent): void {
    if (e.key === 'F11') {
      e.preventDefault();
      this.toggleFullscreen();
    } else if (e.key === 'Escape' && this.isFullscreen) {
      this.toggleFullscreen();
    } else if (e.ctrlKey && e.shiftKey && e.key === 'R') {
      e.preventDefault();
      if (this.hasChanges) this.confirmReset();
    }
  }

  onResize(): void {
    if (this._editor) this._editor.layout();
  }

  onCodeAndLanguageSet(e: Event): void {
    const custom = e as CustomEvent<{ code?: string; language?: string }>;
    if (custom?.detail) {
      custom.detail.code = this.contents;
      custom.detail.language = this.language;
    }
  }

  onFontSizeChange(): void {
    if (this._editor) {
      this._editor.updateOptions({ fontSize: this.selectedFontSize });
    }
  }
}
</script>

<style lang="scss" scoped>
@import '../../../sass/main.scss';

.monaco-root {
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
  position: relative;
  border: 1px solid var(--monaco-editor-toolbar-border-bottom-color, #d1d5db);

  &.monaco-root--fullscreen {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    width: 100vw !important;
    height: 100vh !important;
    z-index: 9999;
    background: #fff;
    border: none;
  }

  // MonacoThemes.VSDark = 'vs-dark'
  &.vs-dark {
    border-color: #404040;
    &.monaco-root--fullscreen {
      background: #1e1e1e;
    }
  }
}

.editor-toolbar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0 12px;
  height: 40px;
  background: var(--monaco-editor-toolbar-background-color, #fafafa);
  border-bottom: 1px solid
    var(--monaco-editor-toolbar-border-bottom-color, #e5e7eb);
  font-size: 13px;

  .vs-dark & {
    background: var(--vs-dark-background-color, #262626);
    border-bottom-color: #333;
  }
}

.toolbar-filename {
  display: flex;
  align-items: center;
  gap: 8px;
  font-weight: 600;
  color: #1f2937;

  .vs-dark & {
    color: #e5e5e5;
  }
}

.toolbar-right {
  display: flex;
  align-items: center;
  gap: 5px;
}

.toolbar-label {
  font-size: 12px;
  color: #6b7280;
  margin: 0;
  font-weight: 600;

  .vs-dark & {
    color: #9ca3af;
  }
}

.toolbar-select {
  border: 1px solid #d1d5db;
  border-radius: 4px;
  padding: 4px 8px;
  font-size: 12px;
  background-color: #fff;
  cursor: pointer;

  .vs-dark & {
    background-color: #262626;
    border-color: #404040;
    color: #d4d4d4;
  }
}

.toolbar-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 28px;
  height: 28px;
  border: none;
  border-radius: 6px;
  background: transparent;
  color: #6b7280;
  cursor: pointer;
  transition: all 0.2s;

  &:hover {
    background: #f3f4f6;
    color: #1f2937;
  }

  &.toolbar-btn--copied {
    color: #059669;
    pointer-events: none;
  }

  .vs-dark & {
    color: #9ca3af;

    &:hover {
      background: rgba(255, 255, 255, 0.1);
      color: #d4d4d4;
    }
  }
}

/* Modal Styles */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 10000;
}

.modal-content {
  background: #fff;
  border-radius: 8px;
  padding: 24px;
  max-width: 400px;
  width: 90%;

  .vs-dark & {
    background: #2a2a2a;
    color: #e5e5e5;
  }

  h3 {
    margin-top: 0;
  }
  p {
    margin-bottom: 20px;
    color: #4b5563;
  }
  .vs-dark p {
    color: #9ca3af;
  }
}

.modal-actions {
  display: flex;
  gap: 12px;
  justify-content: flex-end;
}

.btn {
  padding: 8px 16px;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-weight: 500;
}

.btn-secondary {
  background: #f3f4f6;
  color: #1a1a1a;
}
.btn-danger {
  background: #dc2626;
  color: #fff;
}

.sr-only {
  position: absolute;
  width: 1px;
  height: 1px;
  padding: 0;
  margin: -1px;
  overflow: hidden;
  clip: rect(0, 0, 0, 0);
  border: 0;
}
</style>
