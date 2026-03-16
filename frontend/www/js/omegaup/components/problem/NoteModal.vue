<template>
  <transition name="modal">
    <div
      class="note-modal-mask"
      @click.self="$emit('close')"
      @keydown.esc="$emit('close')"
    >
      <div class="note-modal-container">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <h5 class="mb-0">{{ T.problemNoteTitle }}</h5>
          <button
            type="button"
            class="close"
            aria-label="Close"
            @click="$emit('close')"
          >
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="form-group mb-2">
          <div class="tab-bar">
            <span
              class="tab-btn"
              :class="{ active: activeTab === 'write' }"
              @click="activeTab = 'write'"
            >
              {{ T.wordsEdit }}
            </span>
            <span
              class="tab-btn"
              :class="{ active: activeTab === 'preview' }"
              @click="activeTab = 'preview'"
            >
              {{ T.wordsPreview }}
            </span>
          </div>
          <div v-show="activeTab === 'write'">
            <div class="toolbar">
              <button
                type="button"
                class="toolbar-btn"
                title="Bold"
                @click="wrapSelection('**', '**')"
              >
                <font-awesome-icon :icon="['fas', 'bold']" />
              </button>
              <button
                type="button"
                class="toolbar-btn"
                title="Italic"
                @click="wrapSelection('_', '_')"
              >
                <font-awesome-icon :icon="['fas', 'italic']" />
              </button>
              <button
                type="button"
                class="toolbar-btn"
                title="Strikethrough"
                @click="wrapSelection('~~', '~~')"
              >
                <font-awesome-icon :icon="['fas', 'strikethrough']" />
              </button>
              <span class="toolbar-divider"></span>
              <button
                type="button"
                class="toolbar-btn"
                title="Heading"
                @click="insertPrefix('### ')"
              >
                <font-awesome-icon :icon="['fas', 'heading']" />
              </button>
              <button
                type="button"
                class="toolbar-btn"
                title="Bullet list"
                @click="insertPrefix('- ')"
              >
                <font-awesome-icon :icon="['fas', 'list-ul']" />
              </button>
              <button
                type="button"
                class="toolbar-btn"
                title="Numbered list"
                @click="insertPrefix('1. ')"
              >
                <font-awesome-icon :icon="['fas', 'list-ol']" />
              </button>
              <span class="toolbar-divider"></span>
              <button
                type="button"
                class="toolbar-btn"
                title="Inline code"
                @click="wrapSelection('`', '`')"
              >
                <font-awesome-icon :icon="['fas', 'code']" />
              </button>
              <button
                type="button"
                class="toolbar-btn"
                title="Code block"
                @click="insertCodeBlock"
              >
                <span class="code-block-icon">{}</span>
              </button>
              <button
                type="button"
                class="toolbar-btn"
                title="Link"
                @click="insertLink"
              >
                <font-awesome-icon :icon="['fas', 'link']" />
              </button>
            </div>
            <textarea
              ref="textarea"
              v-model="currentNoteText"
              class="note-textarea"
              rows="10"
              :maxlength="MAX_NOTE_LENGTH"
              :placeholder="T.problemNotePlaceholder"
            ></textarea>
          </div>
          <div v-show="activeTab === 'preview'" class="preview-pane">
            <omegaup-markdown
              v-if="currentNoteText.length > 0"
              :markdown="currentNoteText"
              :full-width="true"
            ></omegaup-markdown>
            <p v-else class="text-muted font-italic">
              {{ T.problemNotePlaceholder }}
            </p>
          </div>
          <small class="form-text text-muted text-right d-block">
            {{ currentNoteText.length }} / {{ MAX_NOTE_LENGTH }} &mdash;
            {{ T.problemNoteCharLimit }}
          </small>
        </div>
        <div class="d-flex justify-content-between">
          <button
            v-if="hasExistingNote"
            class="btn btn-outline-danger"
            :disabled="isSaving"
            @click="onDelete"
          >
            {{ T.wordsDelete }}
          </button>
          <span v-else></span>
          <div>
            <button class="btn btn-secondary mr-2" @click="$emit('close')">
              {{ T.wordsCancel }}
            </button>
            <button
              class="btn btn-primary"
              :disabled="isSaving || currentNoteText.length === 0"
              @click="onSave"
            >
              {{ hasExistingNote ? T.problemNoteEdit : T.problemNoteAdd }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </transition>
</template>

<script lang="ts">
import { Vue, Component, Prop, Watch } from 'vue-property-decorator';
import T from '../../lang';

import { library } from '@fortawesome/fontawesome-svg-core';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import {
  faBold,
  faItalic,
  faStrikethrough,
  faHeading,
  faListUl,
  faListOl,
  faCode,
  faLink,
} from '@fortawesome/free-solid-svg-icons';
library.add(
  faBold,
  faItalic,
  faStrikethrough,
  faHeading,
  faListUl,
  faListOl,
  faCode,
  faLink,
);

import omegaup_Markdown from '../Markdown.vue';

@Component({
  components: {
    FontAwesomeIcon,
    'omegaup-markdown': omegaup_Markdown,
  },
})
export default class NoteModal extends Vue {
  private static readonly MAX_NOTE_LENGTH = 2000;

  @Prop({ default: '' }) initialNoteText!: string;
  @Prop({ required: true }) problemAlias!: string;
  @Prop({ default: 0 }) operationFailed!: number;

  T = T;
  MAX_NOTE_LENGTH = NoteModal.MAX_NOTE_LENGTH;
  currentNoteText = '';
  isSaving = false;
  activeTab: 'write' | 'preview' = 'write';

  $refs!: {
    textarea: HTMLTextAreaElement;
  };

  mounted(): void {
    this.currentNoteText = this.initialNoteText;
    if (this.initialNoteText.length > 0) {
      this.activeTab = 'preview';
    }
  }

  @Watch('operationFailed')
  onOperationFailed(): void {
    this.isSaving = false;
  }

  get hasExistingNote(): boolean {
    return this.initialNoteText.length > 0;
  }

  wrapSelection(before: string, after: string): void {
    const ta = this.$refs.textarea;
    const start = ta.selectionStart;
    const end = ta.selectionEnd;
    const selected = this.currentNoteText.substring(start, end);
    const replacement = `${before}${selected || 'text'}${after}`;
    this.currentNoteText =
      this.currentNoteText.substring(0, start) +
      replacement +
      this.currentNoteText.substring(end);
    this.$nextTick(() => {
      ta.focus();
      const newCursorStart = start + before.length;
      const newCursorEnd = newCursorStart + (selected || 'text').length;
      ta.setSelectionRange(newCursorStart, newCursorEnd);
    });
  }

  insertPrefix(prefix: string): void {
    const ta = this.$refs.textarea;
    const start = ta.selectionStart;
    const lineStart = this.currentNoteText.lastIndexOf('\n', start - 1) + 1;
    this.currentNoteText =
      this.currentNoteText.substring(0, lineStart) +
      prefix +
      this.currentNoteText.substring(lineStart);
    this.$nextTick(() => {
      ta.focus();
      ta.setSelectionRange(start + prefix.length, start + prefix.length);
    });
  }

  insertCodeBlock(): void {
    const ta = this.$refs.textarea;
    const start = ta.selectionStart;
    const end = ta.selectionEnd;
    const selected = this.currentNoteText.substring(start, end);
    const code = selected || 'code';
    const replacement = `\n\`\`\`\n${code}\n\`\`\`\n`;
    this.currentNoteText =
      this.currentNoteText.substring(0, start) +
      replacement +
      this.currentNoteText.substring(end);
    this.$nextTick(() => {
      ta.focus();
      const codeStart = start + 5; // after \n```\n
      const codeEnd = codeStart + code.length;
      ta.setSelectionRange(codeStart, codeEnd);
    });
  }

  insertLink(): void {
    const ta = this.$refs.textarea;
    const start = ta.selectionStart;
    const end = ta.selectionEnd;
    const selected = this.currentNoteText.substring(start, end);
    const linkText = selected || 'link text';
    const replacement = `[${linkText}](url)`;
    this.currentNoteText =
      this.currentNoteText.substring(0, start) +
      replacement +
      this.currentNoteText.substring(end);
    this.$nextTick(() => {
      ta.focus();
      const urlStart = start + linkText.length + 3;
      ta.setSelectionRange(urlStart, urlStart + 3);
    });
  }

  onSave(): void {
    if (this.currentNoteText.length === 0 || this.isSaving) {
      return;
    }
    this.isSaving = true;
    this.$emit('save-note', this.problemAlias, this.currentNoteText);
  }

  onDelete(): void {
    if (this.isSaving) {
      return;
    }
    this.isSaving = true;
    this.$emit('delete-note', this.problemAlias);
  }
}
</script>

<style lang="scss" scoped>
@import '../../../../sass/main.scss';

.note-modal-mask {
  position: fixed;
  z-index: 99999;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0, 0, 0, 0.5);
  transition: opacity 0.3s ease;
  display: flex;
  align-items: center;
  justify-content: center;
}

.note-modal-container {
  background: var(--finder-wizard-modal-container-background-color, #fff);
  min-width: 340px;
  max-width: 800px;
  width: 95%;
  border: 1px solid var(--finder-wizard-modal-container-border-color, #dee2e6);
  border-radius: 0.3rem;
  padding: 1.5em;
  position: relative;
}

.tab-bar {
  display: flex;
  border-bottom: 1px solid
    var(--finder-wizard-modal-container-border-color, #dee2e6);
  margin-bottom: 0;
}

.tab-btn {
  background: transparent !important;
  border: 1px solid transparent;
  border-bottom: none;
  border-radius: 0.25rem 0.25rem 0 0;
  padding: 0.5rem 1rem;
  cursor: pointer;
  color: var(--note-modal-text-muted, #6c757d) !important;
  font-size: 0.9rem;
  margin-bottom: -1px;

  &:hover {
    color: var(--note-modal-text-color, #495057) !important;
    border-color: var(--note-modal-hover-border, #e9ecef)
      var(--note-modal-hover-border, #e9ecef)
      var(--finder-wizard-modal-container-border-color, #dee2e6);
  }

  &.active {
    color: var(--note-modal-text-color, #495057) !important;
    background: var(
      --finder-wizard-modal-container-background-color,
      #fff
    ) !important;
    border-color: var(--finder-wizard-modal-container-border-color, #dee2e6)
      var(--finder-wizard-modal-container-border-color, #dee2e6)
      var(--finder-wizard-modal-container-background-color, #fff);
    font-weight: 600;
  }
}

.toolbar {
  display: flex;
  align-items: center;
  background: var(--note-modal-toolbar-bg, #f8f9fa);
  border: 1px solid var(--finder-wizard-modal-container-border-color, #dee2e6);
  border-bottom: none;
  border-top: none;
  padding: 0.25rem 0.5rem;
  gap: 0.15rem;
}

.toolbar-btn {
  background: none;
  border: 1px solid transparent;
  border-radius: 0.2rem;
  padding: 0.3rem 0.5rem;
  cursor: pointer;
  color: var(--note-modal-text-color, #495057);
  font-size: 0.9rem;
  line-height: 1;

  &:hover {
    background: var(--note-modal-hover-border, #e9ecef);
    border-color: var(--finder-wizard-modal-container-border-color, #dee2e6);
  }

  &:active {
    background: var(--finder-wizard-modal-container-border-color, #dee2e6);
  }
}

.code-block-icon {
  font-family: monospace;
  font-size: 0.85rem;
  font-weight: bold;
}

.toolbar-divider {
  width: 1px;
  height: 1.2rem;
  background: var(--finder-wizard-modal-container-border-color, #dee2e6);
  margin: 0 0.3rem;
}

.note-textarea {
  display: block;
  width: 100%;
  max-width: 100%;
  padding: 0.375rem 0.75rem;
  font-size: 1rem;
  font-weight: 400;
  line-height: 1.5;
  color: var(--note-modal-text-color, #495057);
  background-color: var(--finder-wizard-modal-container-background-color, #fff);
  background-clip: padding-box;
  border: 1px solid var(--note-modal-input-border, #ced4da);
  border-radius: 0 0 0.25rem 0.25rem;
  resize: vertical;
  font-family: inherit;
}

.preview-pane {
  border: 1px solid var(--finder-wizard-modal-container-border-color, #dee2e6);
  border-top: none;
  border-radius: 0 0 0.25rem 0.25rem;
  padding: 1rem;
  min-height: 200px;
  max-height: 400px;
  overflow-y: auto;
  background: var(--finder-wizard-modal-container-background-color, #fff);
}

.modal-enter-active,
.modal-leave-active {
  transition: opacity 0.3s ease;
}

.modal-enter,
.modal-leave-to {
  opacity: 0;
}
</style>
