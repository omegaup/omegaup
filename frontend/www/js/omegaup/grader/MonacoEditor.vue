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
    :aria-label="`Code editor for ${filename}`"
  >
    <div class="editor-toolbar" role="toolbar" aria-label="Editor controls">
      <span class="toolbar-filename" :title="filename">
        <i class="far fa-file-code file-icon" aria-hidden="true"></i>
        {{ filename }}
      </span>
      <div class="toolbar-right">
        <label for="font-size-select" class="toolbar-label">{{
          T.fontSize
        }}</label>
        <select
          v-model="selectedFontSize"
          data-testid="font-size-select"
          class="toolbar-select"
          aria-label="Font size"
          @change="onFontSizeChange"
        >
          <option v-for="size in FONT_SIZES" :key="size" :value="size">
            {{ size }}px
          </option>
        </select>
        <button
          v-if="contents"
          class="toolbar-btn toolbar-btn--copy"
          :class="{ 'toolbar-btn--copied': copied }"
          :title="copyButtonText"
          :aria-label="copyButtonText"
          @click="copyCode"
        >
          <i v-if="!copied" class="far fa-copy" aria-hidden="true"></i>
          <i v-else class="fas fa-check" aria-hidden="true"></i>
          <span class="sr-only">{{ copyButtonText }}</span>
        </button>
        <button
          v-if="!readOnly && hasChanges"
          class="toolbar-btn toolbar-btn--reset"
          title="Reset to default code (Ctrl+Shift+R)"
          aria-label="Reset to default code"
          @click="confirmReset"
        >
          <i class="fas fa-undo" aria-hidden="true"></i>
          <span class="sr-only">Reset</span>
        </button>
        <button
          class="toolbar-btn"
          :title="isFullscreen ? 'Exit fullscreen (Esc)' : 'Fullscreen (F11)'"
          :aria-label="isFullscreen ? 'Exit fullscreen' : 'Enter fullscreen'"
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
      :aria-label="`Code editor content`"
      aria-multiline="true"
    ></div>

    <div
      v-if="showResetModal"
      class="modal-overlay"
      @click.self="showResetModal = false"
    >
      <div
        class="modal-content"
        role="dialog"
        aria-labelledby="reset-modal-title"
        aria-modal="true"
      >
        <h3 aria-label="Reset Code">Reset Code</h3>
        <p>
          Are you sure you want to reset the code to its default state? All
          changes will be lost.
        </p>
        <div class="modal-actions">
          <button class="btn btn-secondary" @click="showResetModal = false">
            Cancel
          </button>
          <button class="btn btn-danger" @click="resetToDefault">Reset</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop, Watch } from 'vue-property-decorator';
import store from './GraderStore';
import * as Util from './util';
import * as monaco from 'monaco-editor';
import T from '../lang';
import { debounce } from 'lodash';
import { EDITOR, TIMING } from './constants';

interface EditorStoreMapping {
  contents: string;
  language: string;
  module: string;
}

@Component
export default class MonacoEditor extends Vue {
  @Prop({ required: true }) storeMapping!: EditorStoreMapping;
  @Prop({ default: false }) readOnly!: boolean;

  private _editor: monaco.editor.IStandaloneCodeEditor | null = null;
  private _model: monaco.editor.ITextModel | null = null;
  selectedFontSize: number = EDITOR.DEFAULT_FONT_SIZE;
  isFullscreen: boolean = false;
  copied: boolean = false;
  showResetModal: boolean = false;
  private copyTimeout: number | null = null;
  private resizeObserver: ResizeObserver | null = null;
  private disposables: monaco.IDisposable[] = [];
  defaultContents: string = '';

  readonly FONT_SIZES = EDITOR.FONT_SIZES;
  readonly T = T;
  get theme(): string {
    return store.getters['theme'];
  }
  get copyButtonText(): string {
    return this.copied ? 'Copied!' : 'Copy code';
  }
  get hasChanges(): boolean {
    return (
      this.defaultContents !== '' && this.contents !== this.defaultContents
    );
  }
  get language(): string {
    return store.getters[this.storeMapping.language];
  }
  get module(): string {
    return store.getters[this.storeMapping.module];
  }
  get contents(): string {
    return store.getters[this.storeMapping.contents];
  }
  set contents(value: string) {
    store.dispatch(this.storeMapping.contents, value);
  }
  get filename(): string {
    return `${this.module}.${Util.supportedLanguages[this.language].extension}`;
  }

  @Watch('language') onLanguageChange(value: string): void {
    if (this._model)
      monaco.editor.setModelLanguage(
        this._model,
        Util.supportedLanguages[value].modelMapping,
      );
  }
  @Watch('contents') onContentsChange(value: string): void {
    if (this._model && this._model.getValue() !== value)
      this._model.setValue(value);
  }
  @Watch('theme') onThemeChange(value: string): void {
    if (this._editor) this._editor.updateOptions({ theme: value });
  }

  async copyCode(): Promise<void> {
    if (!this.contents) return;
    try {
      await navigator.clipboard.writeText(this.contents);
      this.showCopyFeedback();
    } catch (err) {
      this.fallbackCopy();
    }
  }
  private showCopyFeedback(): void {
    this.copied = true;
    this.clearCopyTimeout();
    this.copyTimeout = window.setTimeout(() => {
      this.copied = false;
      this.copyTimeout = null;
    }, TIMING.COPY_FEEDBACK_DURATION_MS);
  }
  private clearCopyTimeout(): void {
    if (this.copyTimeout) {
      clearTimeout(this.copyTimeout);
      this.copyTimeout = null;
    }
  }
  private fallbackCopy(): void {
    if (this._editor) {
      const model = this._editor.getModel();
      if (model) {
        this._editor.setSelection(model.getFullModelRange());
        try {
          document.execCommand('copy');
          this.showCopyFeedback();
        } catch (e) {
          this.$emit('error', { message: 'Failed to copy code' });
        }
      }
    }
  }

  confirmReset(): void {
    this.showResetModal = true;
  }
  resetToDefault(): void {
    this.contents = this.defaultContents;
    this.showResetModal = false;
    this.$emit('reset');
  }
  toggleFullscreen(): void {
    this.isFullscreen = !this.isFullscreen;
    if (this.isFullscreen) {
      document.body.style.overflow = 'hidden';
      this.$emit('fullscreen-enter');
    } else {
      document.body.style.overflow = '';
      this.$emit('fullscreen-exit');
    }
    setTimeout(() => {
      this.onResize();
    }, 100);
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

  private onContentChange = debounce((content: string) => {
    store.dispatch(this.storeMapping.contents, content);
  }, TIMING.DEBOUNCE_EDITOR_CHANGE_MS);

  mounted(): void {
    window.addEventListener('code-and-language-set', this.onCodeAndLanguageSet);
    document.addEventListener('keydown', this.handleKeydown);
    const container = this.$refs.editorContainer as HTMLElement;
    if (!container) return;
    this.defaultContents = this.contents;

    this._editor = monaco.editor.create(container, {
      autoIndent: 'brackets',
      formatOnPaste: true,
      formatOnType: true,
      language: Util.supportedLanguages[this.language].modelMapping,
      readOnly: this.readOnly,
      theme: this.theme,
      value: this.contents,
      fontSize: this.selectedFontSize,
      lineHeight: 20,
      fontFamily: "'JetBrains Mono', 'Fira Code', 'SF Mono', Monaco, monospace",
      minimap: { enabled: true },
      scrollBeyondLastLine: false,
      smoothScrolling: true,
      cursorBlinking: 'smooth',
      cursorSmoothCaretAnimation: true,
      accessibilitySupport: 'on',
      screenReaderAnnounceInlineSuggestion: true,
    } as monaco.editor.IStandaloneEditorConstructionOptions);

    this._model = this._editor.getModel();
    if (this._model)
      this.disposables.push(
        this._model.onDidChangeContent(() => {
          this.onContentChange(this._model?.getValue() || '');
        }),
      );

    if (window.ResizeObserver) {
      this.resizeObserver = new ResizeObserver(() => {
        this.onResize();
      });
      this.resizeObserver.observe(this.$el as HTMLElement);
    } else {
      window.addEventListener('resize', this.onResize);
    }
    this.onResize();
    this.$emit('mounted');
  }

  beforeDestroy(): void {
    this.clearCopyTimeout();
    window.removeEventListener(
      'code-and-language-set',
      this.onCodeAndLanguageSet,
    );
    document.removeEventListener('keydown', this.handleKeydown);
    if (this.resizeObserver) {
      this.resizeObserver.disconnect();
      this.resizeObserver = null;
    } else {
      window.removeEventListener('resize', this.onResize);
    }
    if (this.isFullscreen) document.body.style.overflow = '';
    this.disposables.forEach((d) => d.dispose());
    this.disposables = [];
    if (this._editor) {
      this._editor.dispose();
      this._editor = null;
    }
    if (this._model) {
      this._model.dispose();
      this._model = null;
    }
    this.$emit('destroyed');
  }

  onResize(): void {
    if (this._editor) this._editor.layout();
  }
  onCodeAndLanguageSet(e: Event): void {
    const custom = e as CustomEvent<{ code?: string; language?: string }>;
    if (!custom?.detail) return;
    custom.detail.code = this.contents;
    custom.detail.language = this.language;
  }
  onFontSizeChange(): void {
    if (this._editor) {
      this._editor.updateOptions({ fontSize: this.selectedFontSize });
      this.$emit('font-size-change', this.selectedFontSize);
    }
  }
}
</script>

<style lang="scss" scoped>
.monaco-root {
  display: flex;
  flex-direction: column;
  height: 100%;
  width: 100%;
  border: 1px solid #d1d5db;
  border-radius: 6px;
  overflow: hidden;
  background: #fff;

  &.vs-dark {
    border-color: #404040;
    background: #1e1e1e;
  }

  &--fullscreen {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    width: 100vw !important;
    height: 100vh !important;
    z-index: 9999;
    border-radius: 0;
    border: none;
  }
}

.editor-toolbar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 8px 12px;
  background: #f9fafb;
  border-bottom: 1px solid #e5e7eb;
  min-height: 40px;
  flex-shrink: 0;

  .vs-dark & {
    background: #252525;
    border-bottom-color: #333;
  }
}

.toolbar-filename {
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
  font-size: 13px;
  font-weight: 600;
  color: #4b5563;
  display: flex;
  align-items: center;
  gap: 6px;

  .file-icon {
    color: #6b7280;
  }

  .vs-dark & {
    color: #d1d5db;

    .file-icon {
      color: #9ca3af;
    }
  }
}

.toolbar-right {
  display: flex;
  align-items: center;
  gap: 12px;
}

.toolbar-label {
  font-size: 12px;
  color: #6b7280;
  margin: 0;

  .vs-dark & {
    color: #9ca3af;
  }
}

.toolbar-select {
  appearance: none;
  -webkit-appearance: none;
  padding: 4px 24px 4px 8px;
  border: 1px solid #d1d5db;
  border-radius: 4px;
  background: #fff
    url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='8' height='4' viewBox='0 0 8 4'%3E%3Cpath fill='%236b7280' d='M0 0l4 4 4-4z'/%3E%3C/svg%3E")
    no-repeat right 8px center;
  font-size: 12px;
  color: #374151;
  cursor: pointer;
  outline: none;

  &:focus {
    border-color: #3b82f6;
    box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.1);
  }

  .vs-dark & {
    background-color: #333;
    border-color: #4b5563;
    color: #d1d5db;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='8' height='4' viewBox='0 0 8 4'%3E%3Cpath fill='%239ca3af' d='M0 0l4 4 4-4z'/%3E%3C/svg%3E");
  }
}

.toolbar-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 28px;
  height: 28px;
  border: none;
  background: transparent;
  color: #6b7280;
  border-radius: 4px;
  cursor: pointer;
  transition: all 0.15s;

  &:hover {
    background: #e5e7eb;
    color: #1f2937;
  }

  &:focus-visible {
    outline: 2px solid #3b82f6;
  }

  .vs-dark & {
    color: #9ca3af;

    &:hover {
      background: #404040;
      color: #f3f4f6;
    }
  }

  &--copied {
    color: #10b981 !important;

    .vs-dark & {
      color: #34d399 !important;
    }
  }

  &--reset:hover {
    color: #dc2626;
    background: rgba(220, 38, 38, 0.1);

    .vs-dark & {
      color: #f87171;
      background: rgba(248, 113, 113, 0.15);
    }
  }
}

.editor {
  flex: 1;
  width: 100%;
  min-height: 0;
}

/* Modal */
.modal-overlay {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 10000;
  animation: fadeIn 0.15s ease;
}

.modal-content {
  background: #fff;
  border-radius: 12px;
  padding: 24px;
  max-width: 400px;
  width: 90%;
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1),
    0 10px 10px -5px rgba(0, 0, 0, 0.04);
  animation: scaleIn 0.2s ease;

  .vs-dark & {
    background: #2a2a2a;
    color: #e5e5e5;
  }

  h3 {
    margin: 0 0 12px 0;
    font-size: 18px;
    font-weight: 600;
    color: #1a1a1a;

    .vs-dark & {
      color: #e5e5e5;
    }
  }

  p {
    margin: 0 0 20px 0;
    font-size: 14px;
    color: #4b5563;
    line-height: 1.5;

    .vs-dark & {
      color: #9ca3af;
    }
  }
}

@keyframes fadeIn {
  from {
    opacity: 0;
  }
  to {
    opacity: 1;
  }
}

@keyframes scaleIn {
  from {
    opacity: 0;
    transform: scale(0.95);
  }
  to {
    opacity: 1;
    transform: scale(1);
  }
}

.modal-actions {
  display: flex;
  gap: 8px;
  justify-content: flex-end;
}

.btn {
  padding: 8px 16px;
  border: none;
  border-radius: 6px;
  font-size: 14px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.15s;

  &:focus-visible {
    outline: 2px solid #3b82f6;
    outline-offset: 2px;
  }
}

.btn-secondary {
  background: #f3f4f6;
  color: #1a1a1a;

  &:hover {
    background: #e5e7eb;
  }

  .vs-dark & {
    background: #404040;
    color: #e5e5e5;
    &:hover {
      background: #525252;
    }
  }
}

.btn-danger {
  background: #dc2626;
  color: #fff;
  &:hover {
    background: #b91c1c;
  }
}

/* Screen reader only */
.sr-only {
  position: absolute;
  width: 1px;
  height: 1px;
  padding: 0;
  margin: -1px;
  overflow: hidden;
  clip: rect(0, 0, 0, 0);
  white-space: nowrap;
  border-width: 0;
}
</style>
