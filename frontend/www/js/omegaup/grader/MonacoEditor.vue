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

    <!-- Confirmation Modal -->
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
// Constants
import T from '../lang';
import { debounce } from 'lodash';
import { EDITOR, TIMING } from './constants';
const FONT_SIZES = EDITOR.FONT_SIZES;
const DEFAULT_FONT_SIZE = EDITOR.DEFAULT_FONT_SIZE;

// Types
interface EditorStoreMapping {
  contents: string;
  language: string;
  module: string;
}

@Component
export default class MonacoEditor extends Vue {
  @Prop({ required: true }) storeMapping!: EditorStoreMapping;
  @Prop({ default: false }) readOnly!: boolean;

  // Editor instances
  private _editor: monaco.editor.IStandaloneCodeEditor | null = null;
  private _model: monaco.editor.ITextModel | null = null;

  // State
  selectedFontSize: number = DEFAULT_FONT_SIZE;
  isFullscreen: boolean = false;
  copied: boolean = false;
  showResetModal: boolean = false;

  // Cleanup tracking
  private copyTimeout: number | null = null;
  private resizeObserver: ResizeObserver | null = null;
  private disposables: monaco.IDisposable[] = [];

  // Default content for reset
  defaultContents: string = '';

  // Expose constants
  readonly FONT_SIZES = FONT_SIZES;
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

  // Watchers
  @Watch('language')
  onLanguageChange(value: string): void {
    if (this._model) {
      monaco.editor.setModelLanguage(
        this._model,
        Util.supportedLanguages[value].modelMapping,
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

  // Methods
  async copyCode(): Promise<void> {
    if (!this.contents) return;

    try {
      await navigator.clipboard.writeText(this.contents);
      this.showCopyFeedback();
    } catch (err) {
      console.error('Failed to copy using Clipboard API:', err);
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
        const fullRange = model.getFullModelRange();
        this._editor.setSelection(fullRange);
        try {
          document.execCommand('copy');
          this.showCopyFeedback();
        } catch (fallbackErr) {
          console.error('Fallback copy failed:', fallbackErr);
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

    // Re-layout after transition
    setTimeout(() => {
      this.onResize();
    }, 100);
  }

  handleKeydown(e: KeyboardEvent): void {
    // F11 for fullscreen toggle
    if (e.key === 'F11') {
      e.preventDefault();
      this.toggleFullscreen();
    }
    // Escape to exit fullscreen
    else if (e.key === 'Escape' && this.isFullscreen) {
      this.toggleFullscreen();
    }
    // Ctrl+Shift+R for reset
    else if (e.ctrlKey && e.shiftKey && e.key === 'R') {
      e.preventDefault();
      if (this.hasChanges) {
        this.confirmReset();
      }
    }
  }

  // Debounced content change handler
  private onContentChange = debounce((content: string) => {
    store.dispatch(this.storeMapping.contents, content);
  }, TIMING.DEBOUNCE_EDITOR_CHANGE_MS);

  mounted(): void {
    window.addEventListener('code-and-language-set', this.onCodeAndLanguageSet);
    document.addEventListener('keydown', this.handleKeydown);

    const container = this.$refs.editorContainer as HTMLElement;
    if (!container) return;

    // Store the initial default contents
    this.defaultContents = this.contents;

    // Create editor
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
      fontFamily:
        "'JetBrains Mono', 'Fira Code', 'SF Mono', Monaco, 'Cascadia Code', 'Roboto Mono', Consolas, 'Courier New', monospace",
      minimap: { enabled: true },
      scrollBeyondLastLine: false,
      smoothScrolling: true,
      cursorBlinking: 'smooth',
      cursorSmoothCaretAnimation: true,
      // Accessibility
      accessibilitySupport: 'on',
      screenReaderAnnounceInlineSuggestion: true,
    } as monaco.editor.IStandaloneEditorConstructionOptions);

    this._model = this._editor.getModel();
    if (!this._model) return;

    // Track content changes with debouncing
    const disposable = this._model.onDidChangeContent(() => {
      const content = this._model?.getValue() || '';
      this.onContentChange(content);
    });
    this.disposables.push(disposable);

    // Set up resize observer
    if (window.ResizeObserver) {
      this.resizeObserver = new ResizeObserver(() => {
        this.onResize();
      });
      this.resizeObserver.observe(this.$el as HTMLElement);
    } else {
      // Fallback for older browsers
      window.addEventListener('resize', this.onResize);
    }

    this.onResize();
    this.$emit('mounted');
  }

  beforeDestroy(): void {
    // Clear all timeouts
    this.clearCopyTimeout();

    // Remove event listeners
    window.removeEventListener(
      'code-and-language-set',
      this.onCodeAndLanguageSet,
    );
    document.removeEventListener('keydown', this.handleKeydown);

    // Disconnect resize observer
    if (this.resizeObserver) {
      this.resizeObserver.disconnect();
      this.resizeObserver = null;
    } else {
      window.removeEventListener('resize', this.onResize);
    }

    // Restore body overflow if fullscreen
    if (this.isFullscreen) {
      document.body.style.overflow = '';
    }

    // Dispose Monaco resources
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
    if (this._editor) {
      this._editor.layout();
    }
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
@import '../../../sass/main.scss';

.monaco-root {
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
  position: relative;

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
    animation: fadeIn 0.2s ease;

    &.vs-dark {
      background: #1e1e1e;
    }
  }

  // Focus visible styles for accessibility
  &:focus-within {
    outline: 2px solid #3b82f6;
    outline-offset: -2px;
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

.editor-toolbar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0 12px;
  height: 36px;
  min-height: 36px;
  background: #fafafa;
  border-bottom: 1px solid #e5e7eb;
  font-size: 12px;
  user-select: none;

  .vs-dark & {
    background: #262626;
    border-bottom-color: #333;
  }

  .monaco-root--fullscreen & {
    height: 40px;
    min-height: 40px;
    padding: 0 16px;
  }
}

.toolbar-filename {
  display: flex;
  align-items: center;
  gap: 8px;
  font-weight: 600;
  font-size: 13px;
  color: #1f2937;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
  letter-spacing: -0.01em;

  .vs-dark & {
    color: #e5e5e5;
  }
}

.file-icon {
  flex-shrink: 0;
  color: #6b7280;
  font-size: 16px;

  .vs-dark & {
    color: #9ca3af;
  }
}

.toolbar-right {
  display: flex;
  align-items: center;
  gap: 10px;
  flex-shrink: 0;
}

.toolbar-label {
  font-size: 11px;
  color: #6b7280;
  margin: 0;
  font-weight: 600;

  .vs-dark & {
    color: #9ca3af;
  }
}

.toolbar-select {
  appearance: none;
  -webkit-appearance: none;
  border: 1px solid #d1d5db;
  border-radius: 6px;
  padding: 4px 24px 4px 8px;
  font-size: 12px;
  background: #fff
    url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='6'%3E%3Cpath d='M0 0l5 6 5-6z' fill='%236b7280'/%3E%3C/svg%3E")
    no-repeat right 6px center;
  outline: none;
  cursor: pointer;
  transition: all 0.15s;
  font-weight: 500;
  color: #1f2937;

  &:hover {
    border-color: #9ca3af;
  }

  &:focus {
    border-color: #3b82f6;
    box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.1);
  }

  .vs-dark & {
    background-color: #262626;
    border-color: #404040;
    color: #d4d4d4;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='6'%3E%3Cpath d='M0 0l5 6 5-6z' fill='%239ca3af'/%3E%3C/svg%3E");

    &:hover {
      border-color: #525252;
    }
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
  font-size: 14px;

  i {
    display: flex;
    align-items: center;
    justify-content: center;
  }

  &:hover:not(:disabled) {
    background: #f3f4f6;
    color: #1f2937;
  }

  &:focus-visible {
    outline: 2px solid #3b82f6;
    outline-offset: 2px;
  }

  &:disabled {
    opacity: 0.3;
    cursor: not-allowed;
  }

  &.toolbar-btn--copy {
    &:hover:not(:disabled) {
      color: #3b82f6;
    }
  }

  &.toolbar-btn--reset {
    &:hover:not(:disabled) {
      color: #f59e0b;
      background: rgba(245, 158, 11, 0.1);
    }

    .vs-dark & {
      &:hover:not(:disabled) {
        color: #fbbf24;
        background: rgba(251, 191, 36, 0.15);
      }
    }
  }

  &.toolbar-btn--copied {
    color: #059669;
    background: rgba(16, 185, 129, 0.12);
    pointer-events: none;

    .vs-dark & {
      color: #34d399;
      background: rgba(52, 211, 153, 0.15);
    }
  }

  .vs-dark & {
    color: #9ca3af;

    &:hover:not(:disabled) {
      background: rgba(255, 255, 255, 0.05);
      color: #d4d4d4;
    }

    &.toolbar-btn--copy:hover:not(:disabled) {
      color: #60a5fa;
    }
  }
}

.editor {
  border: none;
}

.monaco-root--fullscreen .editor {
  height: calc(100vh - 40px) !important;
}

// Screen reader only text
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

// Modal styles
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
</style>
