<template>
  <div class="text-editor-wrapper" :class="theme">
    <div
      class="editor-toolbar"
      role="toolbar"
      :aria-label="
        ui.formatString(T.textEditorToolbarAria || 'Toolbar for %(filename)s', {
          filename,
        })
      "
    >
      <div class="toolbar-left">
        <font-awesome-icon
          :icon="fileIcon"
          class="file-icon"
          :class="iconClass"
        />
        <span class="toolbar-filename" :title="filename">{{ filename }}</span>
        <span v-if="lineCount > 0" class="line-badge">{{ lineCount }} L</span>
      </div>

      <div class="toolbar-right">
        <button
          v-if="localContents"
          v-clipboard="() => localContents"
          v-clipboard:success="handleCopyFeedback"
          v-clipboard:error="handleCopyError"
          class="toolbar-btn toolbar-btn--copy"
          :class="{ 'toolbar-btn--copied': copied }"
          :title="
            copied
              ? T.monacoEditorCopied || 'Copied'
              : T.monacoEditorCopyCode || 'Copy code'
          "
          :aria-label="
            copied
              ? T.monacoEditorCopied || 'Copied'
              : T.monacoEditorCopyCode || 'Copy code'
          "
        >
          <font-awesome-icon :icon="copied ? 'check' : 'clipboard'" />
        </button>
      </div>
    </div>

    <div class="editor-body">
      <textarea
        :value="localContents"
        class="te-textarea"
        :disabled="readOnly"
        :aria-label="
          ui.formatString(
            T.textEditorPlainEditorAria || 'Plain text editor for %(filename)s',
            { filename },
          )
        "
        spellcheck="false"
        :placeholder="
          readOnly
            ? T.textEditorNoOutput || 'No output available.'
            : T.textEditorEnterText || 'Enter text here...'
        "
        @input="onInput"
      ></textarea>
    </div>
  </div>
</template>

<script lang="ts">
import { Vue, Prop, Component, Watch } from 'vue-property-decorator';
import store from '../grader/GraderStore';
import T from '../lang';
import * as ui from '../ui';
import { debounce } from 'lodash';

import Clipboard from 'v-clipboard';
import { library } from '@fortawesome/fontawesome-svg-core';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import {
  faClipboard,
  faCheck,
  faFileAlt,
  faExclamationTriangle,
  faTerminal,
  faKeyboard,
} from '@fortawesome/free-solid-svg-icons';

// Register icons
library.add(
  faClipboard,
  faCheck,
  faFileAlt,
  faExclamationTriangle,
  faTerminal,
  faKeyboard,
);
Vue.use(Clipboard);

// Constant to prevent browser crashes from infinite loop outputs
const MAX_RENDER_LENGTH = 100000;

@Component({
  components: {
    'font-awesome-icon': FontAwesomeIcon,
  },
})
export default class TextEditorV2 extends Vue {
  @Prop({ required: true }) storeMapping!: {
    contents: string;
    module?: string;
  };
  @Prop({ required: true }) extension!: string;
  @Prop({ default: 'NA' }) module!: string;
  @Prop({ default: false }) readOnly!: boolean;

  // Local state for instant typing (prevents Vuex lag)
  localContents: string = '';
  copied: boolean = false;

  private copyTimeout: number | null = null;
  T = T;
  ui = ui;

  // --- Debounced Vuex Sync ---
  private syncToStore = debounce((value: string) => {
    store.dispatch(this.storeMapping.contents, value);
  }, 300);

  // --- Computed Properties ---
  get theme(): string {
    return store.getters['theme'] || 'vs-light';
  }

  get filename(): string {
    const baseModule = this.storeMapping.module
      ? store.getters[this.storeMapping.module]
      : this.module;
    return `${baseModule}.${this.extension}`;
  }

  get storeContents(): string {
    return store.getters[this.storeMapping.contents] || '';
  }

  get lineCount(): number {
    return this.localContents ? this.localContents.split('\n').length : 0;
  }

  // Dynamic UI based on file extension
  get fileIcon(): string {
    const ext = this.extension.toLowerCase();
    if (ext === 'err' || ext === 'error') return 'exclamation-triangle';
    if (ext === 'out' || ext === 'output') return 'terminal';
    if (ext === 'in' || ext === 'input') return 'keyboard';
    return 'file-alt';
  }

  get iconClass(): string {
    const ext = this.extension.toLowerCase();
    if (ext === 'err' || ext === 'error') return 'icon-danger';
    if (ext === 'in' || ext === 'input') return 'icon-success';
    return 'icon-muted';
  }

  // --- Watchers ---
  @Watch('storeContents', { immediate: true })
  onStoreContentsChange(newVal: string): void {
    // Truncate massive strings before they crash the textarea
    let safeVal = newVal;
    if (safeVal.length > MAX_RENDER_LENGTH) {
      safeVal =
        safeVal.slice(0, MAX_RENDER_LENGTH) +
        (T.textEditorOutputTruncated ||
          '\n\n... [OUTPUT TRUNCATED: Exceeded character limit]');
    }

    // Only update local state if it differs, preventing cursor jumping while typing
    if (this.localContents !== safeVal) {
      this.localContents = safeVal;
    }
  }

  // --- Methods ---
  onInput(event: Event): void {
    const target = event.target as HTMLTextAreaElement;
    this.localContents = target.value;
    if (!this.readOnly) {
      this.syncToStore(target.value);
    }
  }

  handleCopyFeedback(): void {
    this.copied = true;
    if (this.copyTimeout) clearTimeout(this.copyTimeout);
    this.copyTimeout = window.setTimeout(() => {
      this.copied = false;
    }, 2000);
  }

  handleCopyError(): void {
    if (this.copyTimeout) clearTimeout(this.copyTimeout);
    this.copied = false;
    ui.error(T.monacoEditorClipboardError || 'Failed to copy to clipboard');
  }

  beforeDestroy(): void {
    if (this.copyTimeout) clearTimeout(this.copyTimeout);
    this.syncToStore.cancel(); // Cancel any pending debounced calls
  }
}
</script>

<style lang="scss" scoped>
@import '../../../sass/main.scss';

.text-editor-wrapper {
  display: flex;
  flex-direction: column;
  height: 100%;
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
  border: 1px solid var(--monaco-editor-toolbar-border-bottom-color, #d1d5db);
  background: #fff;
  border-radius: 4px;
  overflow: hidden;

  &.vs-dark {
    border-color: #404040;
    background: var(--vs-dark-background-color, #1e1e1e);
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
  flex-shrink: 0;

  .vs-dark & {
    background: var(--vs-dark-background-color, #262626);
    border-bottom-color: #333;
  }
}

.toolbar-left,
.toolbar-right {
  display: flex;
  align-items: center;
  gap: 8px;
}

.toolbar-filename {
  font-weight: 600;
  color: #1f2937;

  .vs-dark & {
    color: #e5e5e5;
  }
}

/* Dynamic Icon Colors */
.icon-danger {
  color: #ef4444;
}
.icon-success {
  color: #10b981;
}
.icon-muted {
  color: #6b7280;
}

.vs-dark .icon-danger {
  color: #f87171;
}
.vs-dark .icon-success {
  color: #34d399;
}
.vs-dark .icon-muted {
  color: #9ca3af;
}

.line-badge {
  font-size: 10px;
  font-weight: 700;
  background: #e5e7eb;
  color: #4b5563;
  padding: 2px 6px;
  border-radius: 12px;
  margin-left: 4px;

  .vs-dark & {
    background: #404040;
    color: #9ca3af;
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

.editor-body {
  flex: 1;
  min-height: 0;
  position: relative;
}

.te-textarea {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  padding: 16px;
  border: none;
  resize: none;
  background: transparent;
  color: var(--vs-font-color, #1f2937);
  font-family: 'JetBrains Mono', 'Fira Code', monospace;
  font-size: 13px;
  line-height: 1.6;
  outline: none;
  tab-size: 4;

  &:disabled {
    opacity: 0.8;
  }
  &::placeholder {
    color: #9ca3af;
    font-style: italic;
  }

  .vs-dark & {
    color: var(--vs-dark-font-color, #d4d4d4);
    &::placeholder {
      color: #6b7280;
    }
  }
}
</style>
