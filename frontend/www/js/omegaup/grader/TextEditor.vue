<template>
  <div
    class="text-editor"
    :class="[
      theme,
      { 'text-editor--fullscreen': isFullscreen, 'is-focused': isFocused },
    ]"
  >
    <div v-if="showHeader" class="te-header">
      <div class="te-file-info">
        <div class="te-icon-wrapper">
          <i class="far fa-file-code te-file-icon" aria-hidden="true"></i>
        </div>
        <div class="te-filename-stack">
          <span class="te-filename">{{ filename }}</span>
          <span v-if="!readOnly" class="te-status">Editable</span>
        </div>
        <span v-if="!readOnly && lineCount > 0" class="te-badge"
          >{{ lineCount }} L</span
        >
      </div>

      <div class="te-actions">
        <button
          v-if="contents"
          class="te-btn te-btn--copy"
          :class="{ 'is-copied': copied }"
          :title="copyButtonText"
          @click="copyCode"
        >
          <i
            :class="copied ? 'fas fa-check-circle' : 'far fa-copy'"
            aria-hidden="true"
          ></i>
        </button>

        <div v-if="!readOnly && contents" class="te-divider"></div>

        <button
          v-if="!readOnly && contents"
          class="te-btn te-btn--danger"
          title="Clear content"
          @click="clearContents"
        >
          <i class="fas fa-trash-alt" aria-hidden="true"></i>
        </button>

        <button
          class="te-btn"
          :title="isFullscreen ? 'Exit' : 'Maximize'"
          @click="toggleFullscreen"
        >
          <i
            :class="
              isFullscreen
                ? 'fas fa-compress-arrows-alt'
                : 'fas fa-expand-arrows-alt'
            "
            aria-hidden="true"
          ></i>
        </button>
      </div>
    </div>

    <div class="te-body">
      <textarea
        ref="textarea"
        v-model="contents"
        class="te-textarea"
        :disabled="readOnly"
        :data-title="filename"
        :placeholder="
          readOnly ? 'No output available.' : 'Write code or test cases here...'
        "
        spellcheck="false"
        @focus="isFocused = true"
        @blur="isFocused = false"
        @input="updateLineCount"
      ></textarea>

      <div v-if="readOnly" class="te-readonly-overlay">
        <i class="fas fa-lock" aria-hidden="true"></i>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { Vue, Prop, Component, Watch } from 'vue-property-decorator';
import store from './GraderStore';

@Component
export default class TextEditor extends Vue {
  @Prop({ required: true }) storeMapping!: Record<string, string>;
  @Prop({ required: true }) extension!: string;
  @Prop({ default: 'NA' }) module!: string;
  @Prop({ default: false }) readOnly!: boolean;
  @Prop({ default: true }) showHeader!: boolean;

  lineCount: number = 0;
  isFullscreen: boolean = false;
  isFocused: boolean = false;
  copied: boolean = false;
  copyTimeout: number | null = null;

  get theme(): string {
    return store.getters['theme'];
  }
  get copyButtonText(): string {
    return this.copied ? 'Saved to clipboard' : 'Copy';
  }
  get filename(): string {
    return this.storeMapping.module
      ? `${store.getters[this.storeMapping.module]}.${this.extension}`
      : `${this.module}.${this.extension}`;
  }
  get contents(): string {
    return store.getters[this.storeMapping.contents];
  }
  set contents(value: string) {
    if (!this.readOnly) store.dispatch(this.storeMapping.contents, value);
  }

  @Watch('contents', { immediate: true })
  onContentsChange(): void {
    this.updateLineCount();
  }

  updateLineCount(): void {
    this.lineCount = this.contents ? this.contents.split('\n').length : 0;
  }

  clearContents(): void {
    if (confirm('Clear everything?')) this.contents = '';
  }

  async copyCode(): Promise<void> {
    if (!this.contents) return;
    try {
      await navigator.clipboard.writeText(this.contents);
      this.copied = true;
      if (this.copyTimeout) clearTimeout(this.copyTimeout);
      this.copyTimeout = window.setTimeout(() => {
        this.copied = false;
        this.copyTimeout = null;
      }, 2000);
    } catch (err) {
      // Fallback for older environments
    }
  }

  toggleFullscreen(): void {
    this.isFullscreen = !this.isFullscreen;
    document.body.style.overflow = this.isFullscreen ? 'hidden' : '';
  }

  mounted(): void {
    this.updateLineCount();
    document.addEventListener('keydown', this.handleEscapeKey);
  }

  beforeDestroy(): void {
    document.removeEventListener('keydown', this.handleEscapeKey);
    if (this.copyTimeout) clearTimeout(this.copyTimeout);
  }

  handleEscapeKey(e: KeyboardEvent): void {
    if (e.key === 'Escape' && this.isFullscreen) this.toggleFullscreen();
  }
}
</script>

<style lang="scss" scoped>
.text-editor {
  --te-primary: #3b82f6;
  --te-bg: #fff;
  --te-header: #f8fafc;
  --te-border: #e2e8f0;
  --te-text: #334155;
  --te-text-dim: #94a3b8;
  --te-font: 'JetBrains Mono', 'Fira Code', monospace;

  display: flex;
  flex-direction: column;
  height: 100%;
  background: var(--te-bg);
  border: 1px solid var(--te-border);
  border-radius: 8px;
  overflow: hidden;
  transition: border-color 0.2s ease, box-shadow 0.2s ease;

  &.is-focused {
    border-color: var(--te-primary);
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
  }

  &.vs-dark {
    --te-primary: #60a5fa;
    --te-bg: #0f172a;
    --te-header: #1e293b;
    --te-border: #334155;
    --te-text: #f1f5f9;
    --te-text-dim: #64748b;
  }

  &--fullscreen {
    position: fixed;
    inset: 0;
    width: 100vw;
    height: 100vh;
    z-index: 9999;
    border-radius: 0;
  }
}

.te-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 10px 14px;
  background: var(--te-header);
  border-bottom: 1px solid var(--te-border);
}

.te-file-info {
  display: flex;
  align-items: center;
  gap: 12px;
}

.te-icon-wrapper {
  background: rgba(59, 130, 246, 0.1);
  padding: 6px;
  border-radius: 6px;
  display: flex;
  .vs-dark & {
    background: rgba(96, 165, 250, 0.1);
  }
}

.te-file-icon {
  color: var(--te-primary);
  font-size: 14px;
}

.te-filename-stack {
  display: flex;
  flex-direction: column;
}

.te-filename {
  font-size: 13px;
  font-weight: 700;
  color: var(--te-text);
  line-height: 1.2;
}

.te-status {
  font-size: 10px;
  color: var(--te-text-dim);
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.te-badge {
  font-size: 10px;
  font-weight: 800;
  background: var(--te-border);
  color: var(--te-text-dim);
  padding: 2px 8px;
  border-radius: 4px;
}

.te-actions {
  display: flex;
  align-items: center;
  gap: 6px;
}

.te-divider {
  width: 1px;
  height: 16px;
  background: var(--te-border);
  margin: 0 4px;
}

.te-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 32px;
  height: 32px;
  border: none;
  background: transparent;
  color: var(--te-text-dim);
  border-radius: 6px;
  cursor: pointer;
  transition: all 0.2s;

  &:hover {
    background: var(--te-border);
    color: var(--te-text);
  }

  &.is-copied {
    color: #22c55e !important;
  }
  &--danger:hover {
    background: #fee2e2;
    color: #ef4444;
  }
}

.te-body {
  flex: 1;
  position: relative;
}

.te-textarea {
  position: absolute;
  inset: 0;
  width: 100%;
  height: 100%;
  padding: 20px;
  border: none;
  resize: none;
  background: transparent;
  color: var(--te-text);
  font-family: var(--te-font);
  font-size: 14px;
  line-height: 1.7;
  outline: none;
  tab-size: 4;

  &::placeholder {
    color: var(--te-text-dim);
    font-style: italic;
    opacity: 0.5;
  }
}

.te-readonly-overlay {
  position: absolute;
  bottom: 12px;
  right: 12px;
  color: var(--te-text-dim);
  opacity: 0.3;
  pointer-events: none;
}

/* Scrollbar styling */
.te-textarea::-webkit-scrollbar {
  width: 8px;
  height: 8px;
}

.te-textarea::-webkit-scrollbar-thumb {
  background: var(--te-border);
  border-radius: 10px;
  &:hover {
    background: var(--te-text-dim);
  }
}
</style>
