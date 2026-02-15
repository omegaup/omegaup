<template>
  <div class="text-editor" :class="theme">
    <div v-if="showHeader" class="editor-header">
      <div class="file-info">
        <i class="far fa-file-code file-icon"></i>
        <span class="filename">{{ filename }}</span>
        <span v-if="!readOnly && lineCount > 0" class="line-count"
          >{{ lineCount }} lines</span
        >
      </div>
      <div class="header-actions">
        <button
          v-if="contents"
          class="action-btn action-btn--copy"
          :class="{ 'action-btn--copied': copied }"
          :title="copyButtonText"
          @click="copyCode"
        >
          <i v-if="!copied" class="far fa-copy"></i>
          <i v-else class="fas fa-check"></i>
        </button>
        <button
          v-if="!readOnly && contents"
          class="action-btn"
          title="Clear"
          @click="clearContents"
        >
          <i class="fas fa-trash-alt"></i>
        </button>
        <button
          class="action-btn"
          :title="isFullscreen ? 'Exit fullscreen' : 'Fullscreen'"
          @click="toggleFullscreen"
        >
          <i v-if="!isFullscreen" class="fas fa-expand"></i>
          <i v-else class="fas fa-compress"></i>
        </button>
        <div v-if="readOnly" class="readonly-badge">
          <i class="fas fa-lock"></i>
          Read-only
        </div>
      </div>
    </div>
    <div class="editor-container">
      <textarea
        ref="textarea"
        v-model="contents"
        class="editor-content"
        :disabled="readOnly"
        :placeholder="
          readOnly ? 'No output' : 'Type or paste your code here...'
        "
        spellcheck="false"
        @input="updateLineCount"
      ></textarea>
    </div>
  </div>
</template>

<script lang="ts">
import { Vue, Prop, Component, Watch } from 'vue-property-decorator';
import store from './GraderStore';

@Component
export default class TextEditor extends Vue {
  @Prop({ required: true }) storeMapping!: { [key: string]: string };
  @Prop({ required: true }) extension!: string;
  @Prop({ default: 'NA' }) module!: string;
  @Prop({ default: false }) readOnly!: boolean;
  @Prop({ default: true }) showHeader!: boolean;

  lineCount: number = 0;
  isFullscreen: boolean = false;
  copied: boolean = false;
  copyTimeout: number | null = null;

  get theme(): string {
    return store.getters['theme'];
  }

  get copyButtonText(): string {
    return this.copied ? 'Copied!' : 'Copy code';
  }

  get filename(): string {
    if (this.storeMapping.module) {
      return `${store.getters[this.storeMapping.module]}.${this.extension}`;
    }
    return `${this.module}.${this.extension}`;
  }

  get contents(): string {
    return store.getters[this.storeMapping.contents];
  }

  set contents(value: string) {
    if (this.readOnly) return;
    store.dispatch(this.storeMapping.contents, value);
  }

  get title(): string {
    return this.filename;
  }

  @Watch('contents', { immediate: true })
  onContentsChange(): void {
    this.updateLineCount();
  }

  updateLineCount(): void {
    if (this.contents) {
      this.lineCount = this.contents.split('\n').length;
    } else {
      this.lineCount = 0;
    }
  }

  clearContents(): void {
    if (confirm('Are you sure you want to clear all content?')) {
      this.contents = '';
    }
  }

  async copyCode(): Promise<void> {
    if (!this.contents) return;

    try {
      await navigator.clipboard.writeText(this.contents);
      this.copied = true;

      if (this.copyTimeout) {
        clearTimeout(this.copyTimeout);
      }

      this.copyTimeout = window.setTimeout(() => {
        this.copied = false;
        this.copyTimeout = null;
      }, 2000);
    } catch (err) {
      console.error('Failed to copy:', err);
      // Fallback for older browsers
      const textarea = this.$refs.textarea as HTMLTextAreaElement;
      if (textarea) {
        textarea.select();
        try {
          document.execCommand('copy');
          this.copied = true;
          setTimeout(() => {
            this.copied = false;
          }, 2000);
        } catch (fallbackErr) {
          console.error('Fallback copy failed:', fallbackErr);
        }
      }
    }
  }

  toggleFullscreen(): void {
    this.isFullscreen = !this.isFullscreen;
    const editorElement = this.$el as HTMLElement;

    if (this.isFullscreen) {
      editorElement.classList.add('text-editor--fullscreen');
      document.body.style.overflow = 'hidden';
    } else {
      editorElement.classList.remove('text-editor--fullscreen');
      document.body.style.overflow = '';
    }
  }

  syncLineNumbers(): void {
    // Future enhancement: sync line numbers if we add a gutter
  }

  mounted(): void {
    this.updateLineCount();
    document.addEventListener('keydown', this.handleEscapeKey);
  }

  beforeDestroy(): void {
    document.removeEventListener('keydown', this.handleEscapeKey);
    if (this.isFullscreen) {
      document.body.style.overflow = '';
    }
    if (this.copyTimeout) {
      clearTimeout(this.copyTimeout);
    }
  }

  handleEscapeKey(e: KeyboardEvent): void {
    if (e.key === 'Escape' && this.isFullscreen) {
      this.toggleFullscreen();
    }
  }
}
</script>

<style lang="scss" scoped>
.text-editor {
  display: flex;
  flex-direction: column;
  height: 100%;
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
  background: #fff;
  border-radius: 8px;
  overflow: hidden;
  position: relative;

  &.vs-dark {
    background: #1e1e1e;
  }

  &.text-editor--fullscreen {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    width: 100vw;
    height: 100vh;
    z-index: 9999;
    border-radius: 0;
    animation: fadeIn 0.2s ease;
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

.editor-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 8px 16px;
  border-bottom: 1px solid #e5e7eb;
  background: #fafafa;
  min-height: 40px;

  .vs-dark & {
    background: #262626;
    border-bottom-color: #333;
  }
}

.file-info {
  display: flex;
  align-items: center;
  gap: 10px;
  flex: 1;
  min-width: 0;
}

.file-icon {
  color: #6b7280;
  flex-shrink: 0;
  font-size: 16px;

  .vs-dark & {
    color: #9ca3af;
  }
}

.filename {
  font-size: 13px;
  font-weight: 600;
  color: #1f2937;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;

  .vs-dark & {
    color: #e5e5e5;
  }
}

.line-count {
  font-size: 12px;
  font-weight: 500;
  color: #6b7280;
  padding: 2px 8px;
  background: #f3f4f6;
  border-radius: 4px;
  white-space: nowrap;

  .vs-dark & {
    color: #9ca3af;
    background: #374151;
  }
}

.header-actions {
  display: flex;
  align-items: center;
  gap: 8px;
}

.action-btn {
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

  &:hover {
    background: #f3f4f6;
    color: #1f2937;
  }

  &.action-btn--copy {
    &:hover {
      color: #3b82f6;
    }
  }

  &.action-btn--copied {
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

    &:hover {
      background: rgba(255, 255, 255, 0.05);
      color: #d4d4d4;
    }

    &.action-btn--copy:hover {
      color: #60a5fa;
    }
  }
}

.readonly-badge {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 11px;
  font-weight: 600;
  padding: 4px 10px;
  border-radius: 6px;
  background: #fef3c7;
  color: #92400e;
  white-space: nowrap;

  i {
    flex-shrink: 0;
    font-size: 11px;
  }

  .vs-dark & {
    background: rgba(251, 191, 36, 0.15);
    color: #fbbf24;
  }
}

.editor-container {
  flex: 1;
  display: flex;
  position: relative;
  overflow: hidden;
}

.editor-content {
  flex: 1;
  border: none;
  outline: none;
  resize: none;
  padding: 16px;
  font-family: 'JetBrains Mono', 'Fira Code', 'SF Mono', Monaco, 'Cascadia Code',
    'Roboto Mono', Consolas, 'Courier New', monospace;
  font-size: 13px;
  line-height: 1.6;
  background: #fff;
  color: #1f2937;
  tab-size: 4;
  -moz-tab-size: 4;
  white-space: pre;
  overflow-wrap: normal;
  overflow-x: auto;

  &::placeholder {
    color: #9ca3af;
    font-style: italic;
  }

  &:disabled {
    opacity: 0.7;
    cursor: not-allowed;
    background: #fafafa;

    .vs-dark & {
      background: #1a1a1a;
    }
  }

  &:not(:disabled):focus {
    background: #fefefe;

    .vs-dark & {
      background: #1a1a1a;
    }
  }

  .vs-dark & {
    background: #1e1e1e;
    color: #d4d4d4;

    &::placeholder {
      color: #6b7280;
    }
  }
}

/* Enhanced scrollbar styling inspired by LeetCode */
.editor-content::-webkit-scrollbar {
  width: 12px;
  height: 12px;
}

.editor-content::-webkit-scrollbar-track {
  background: transparent;
}

.editor-content::-webkit-scrollbar-thumb {
  background: #cbd5e1;
  border-radius: 6px;
  border: 3px solid transparent;
  background-clip: padding-box;
  transition: background 0.2s;

  &:hover {
    background: #94a3b8;
    background-clip: padding-box;
  }

  &:active {
    background: #64748b;
    background-clip: padding-box;
  }

  .vs-dark & {
    background: #404040;
    background-clip: padding-box;

    &:hover {
      background: #525252;
      background-clip: padding-box;
    }

    &:active {
      background: #6b7280;
      background-clip: padding-box;
    }
  }
}

.editor-content::-webkit-scrollbar-corner {
  background: transparent;
}

/* Firefox scrollbar */

/* Selection styling */
.editor-content::selection {
  background: rgba(59, 130, 246, 0.2);
}

.vs-dark .editor-content::selection {
  background: rgba(96, 165, 250, 0.25);
}
</style>
