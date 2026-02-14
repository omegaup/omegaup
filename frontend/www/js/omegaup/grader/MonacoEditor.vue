<template>
  <div :class="['h-100', 'd-flex', 'flex-column', theme]">
    <div class="editor-toolbar d-flex align-items-center p-1 form-inline">
      <label class="mr-1 mb-0 p-1">{{ T.fontSize }}</label>
      <select
        v-model="selectedFontSize"
        class="custom-select-sm"
        @change="onFontSizeChange"
      >
        <option v-for="size in fontSizes" :key="size" :value="size">
          {{ size }}px
        </option>
      </select>
      <button
        class="btn btn-sm btn-secondary ml-2 template-btn"
        :title="T.graderTemplates"
        @click="openTemplateManager"
      >
        <font-awesome-icon :icon="['fas', 'file-code']" />
      </button>
    </div>

    <!-- Template Manager Modal -->
    <div
      v-if="templateManagerModal.visible"
      class="modal fade show d-block"
      tabindex="-1"
      @click.self="closeTemplateManager"
    >
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">
              {{ T.graderManageTemplates }}
            </h5>
            <button
              type="button"
              class="close"
              @click="closeTemplateManager"
            >
              <span>&times;</span>
            </button>
          </div>

          <div class="modal-body">
            <!-- Info text -->
            <div class="alert alert-info mb-3">
              {{ T.graderTemplateInfoText }}
            </div>

            <!-- Saved Templates List -->
            <div v-if="userTemplates.length > 0" class="mb-4">
              <h6 class="section-title">
                {{ T.graderYourSavedTemplates }}
              </h6>
              <div class="list-group template-list">
                <div
                  v-for="template in userTemplates"
                  :key="template.template_id"
                  class="list-group-item d-flex align-items-center justify-content-between"
                  :class="{ active: templateManagerModal.selectedTemplate && templateManagerModal.selectedTemplate.template_id === template.template_id }"
                >
                  <span>{{ template.template_name }}</span>
                  <span>
                    <button
                      type="button"
                      class="btn btn-sm btn-success mr-1"
                      :title="T.graderLoadTemplate"
                      @click="loadTemplateIntoEditor(template)"
                    >
                      <font-awesome-icon :icon="['fas', 'play']" />
                    </button>
                    <button
                      type="button"
                      class="btn btn-sm btn-outline-primary"
                      :title="T.graderEditTemplate"
                      @click="selectTemplate(template)"
                    >
                      <font-awesome-icon :icon="['fas', 'edit']" />
                    </button>
                  </span>
                </div>
              </div>
            </div>

            <div v-else class="alert alert-warning mb-4">
              {{ T.graderNoTemplatesFound }}
            </div>

            <!-- Template Editor Form -->
            <div class="template-form">
              <h6 class="section-title">
                {{ templateManagerModal.selectedTemplate ? T.graderEditTemplate : T.graderCreateNewTemplate }}
              </h6>

              <div class="form-group">
                <label>
                  {{ T.graderTemplateName }}
                  <span class="text-muted small">({{ T.graderRequired }}, max 100 chars)</span>
                </label>
                <input
                  ref="templateNameInput"
                  v-model="templateManagerModal.templateName"
                  type="text"
                  class="form-control"
                  :placeholder="T.graderTemplateNamePlaceholder"
                  maxlength="100"
                  @keydown.enter.prevent="saveCurrentTemplate"
                />
              </div>

              <div class="form-group">
                <label>
                  {{ T.graderTemplateCode }}
                  <span class="text-muted small">({{ T.graderRequired }})</span>
                </label>
                <textarea
                  v-model="templateManagerModal.templateCode"
                  class="form-control code-textarea"
                  rows="10"
                  :placeholder="T.graderTemplateCodePlaceholder"
                ></textarea>
              </div>
            </div>
          </div>

          <div class="modal-footer">
            <button
              v-if="templateManagerModal.selectedTemplate"
              type="button"
              class="btn btn-danger mr-auto"
              @click="deleteCurrentTemplate"
            >
              <font-awesome-icon :icon="['fas', 'trash']" class="mr-1" />{{ T.wordsDelete }}
            </button>
            <button
              type="button"
              class="btn btn-info"
              @click="useCurrentCode"
              :title="T.graderUseCurrentCodeTooltip"
            >
              <font-awesome-icon :icon="['fas', 'code']" class="mr-1" />{{ T.graderUseCurrentCode }}
            </button>
            <button
              v-if="!templateManagerModal.selectedTemplate"
              type="button"
              class="btn btn-secondary"
              @click="clearForm"
            >
              {{ T.graderClearForm }}
            </button>
            <button
              type="button"
              class="btn btn-outline-secondary"
              @click="closeTemplateManager"
            >
              {{ T.wordsCancel }}
            </button>
            <button
              type="button"
              class="btn btn-primary save-btn"
              @click="saveCurrentTemplate"
            >
              <font-awesome-icon :icon="['fas', 'save']" />
              <span class="ml-1">{{ T.wordsSave }}</span>
            </button>
          </div>
        </div>
      </div>
    </div>
    <div v-if="templateManagerModal.visible" class="modal-backdrop fade show"></div>

    <div ref="editorContainer" class="editor flex-grow-1 w-100 h-100"></div>
  </div>
</template>

<script lang="ts">
// TODO: replace all instances of any with correct type
import { Vue, Component, Prop, Watch } from 'vue-property-decorator';
import store from './GraderStore';
import * as Util from './util';
import * as monaco from 'monaco-editor';
import T from '../lang';
import * as api from '../api';
import * as ui from '../ui';
import { types } from '../api_types';

import { library } from '@fortawesome/fontawesome-svg-core';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import {
  faFileCode,
  faTrash,
  faCode,
  faSave,
  faPlay,
  faEdit,
} from '@fortawesome/free-solid-svg-icons';
library.add(faFileCode, faTrash, faCode, faSave, faPlay, faEdit);

@Component({
  components: {
    FontAwesomeIcon,
  },
})
export default class MonacoEditor extends Vue {
  // TODO: place more restrictions on value of keys inside storeMapping
  @Prop({ required: true }) storeMapping!: {
    [key: string]: string;
  };
  @Prop({ default: false }) readOnly!: boolean;

  _editor: monaco.editor.IStandaloneCodeEditor | null = null;
  _model: monaco.editor.ITextModel | null = null;

  // default font size and line height
  selectedFontSize: number = 12;
  fontSizes: number[] = [12, 14, 16, 18, 20];

  T = T; //getting translations

  // Template management
  userTemplates: types.CodeTemplate[] = [];
  templateManagerModal = {
    visible: false,
    selectedTemplate: null as types.CodeTemplate | null,
    templateName: '',
    templateCode: '',
  };

  get theme(): string {
    return store.getters['theme'];
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

  get title(): string {
    return this.filename;
  }

  @Watch('language')
  async onLanguageChange(value: string): Promise<void> {
    if (this._model) {
      monaco.editor.setModelLanguage(
        this._model,
        Util.supportedLanguages[value].modelMapping,
      );
    }
    // Reload templates when language changes
    await this.loadTemplates();
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
      this._editor.updateOptions({
        theme: value,
      });
    }
  }

  async mounted(): Promise<void> {
    window.addEventListener('code-and-language-set', this.onCodeAndLanguageSet);

    const container = this.$refs.editorContainer as HTMLElement;
    if (!container) return;

    this._editor = monaco.editor.create(container, {
      autoIndent: 'brackets',
      formatOnPaste: true,
      formatOnType: true,
      language: Util.supportedLanguages[this.language].modelMapping,
      readOnly: this.readOnly,
      theme: this.theme,
      value: this.contents,
      fontSize: this.selectedFontSize,
    } as monaco.editor.IStandaloneEditorConstructionOptions);
    this._model = this._editor.getModel();
    if (!this._model) return;

    this._model.onDidChangeContent(() => {
      store.dispatch(this.storeMapping.contents, this._model?.getValue() || '');
    });

    window.addEventListener('resize', this.onResize);
    this.onResize();

    // Load user templates
    await this.loadTemplates();
  }

  beforeDestroy(): void {
    window.removeEventListener(
      'code-and-language-set',
      this.onCodeAndLanguageSet,
    );
    window.removeEventListener('resize', this.onResize);
  }

  onResize(): void {
    if (this._editor) {
      // scaling does not work as intended
      // the cursor does not click where it's supposed to
      // this is an alternative solution to zooming in/out
      this._editor.layout();
    }
  }

  onCodeAndLanguageSet(e: any) {
    e.detail.code = this.contents;
    e.detail.language = this.language;
  }

  onFontSizeChange(): void {
    if (this._editor) {
      this._editor.updateOptions({ fontSize: this.selectedFontSize });
    }
  }

  async loadTemplates(): Promise<void> {
    try {
      const response = await api.CodeTemplate.list({
        language: this.language,
      });
      this.userTemplates = response.templates;
    } catch (error) {
      console.error('Failed to load templates:', error);
      this.userTemplates = [];
    }
  }

  openTemplateManager(): void {
    this.templateManagerModal.visible = true;
    this.templateManagerModal.selectedTemplate = null;
    this.templateManagerModal.templateName = '';
    this.templateManagerModal.templateCode = '';

    // Focus the template name input after modal opens
    this.$nextTick(() => {
      const input = this.$refs.templateNameInput as HTMLInputElement;
      if (input) {
        input.focus();
      }
    });
  }

  loadTemplateIntoEditor(template: types.CodeTemplate): void {
    this.contents = template.code;
    this.closeTemplateManager();
    ui.success(T.graderTemplateLoaded);
  }

  closeTemplateManager(): void {
    this.templateManagerModal.visible = false;
    this.templateManagerModal.selectedTemplate = null;
    this.templateManagerModal.templateName = '';
    this.templateManagerModal.templateCode = '';
  }

  selectTemplate(template: types.CodeTemplate): void {
    this.templateManagerModal.selectedTemplate = template;
    this.templateManagerModal.templateName = template.template_name;
    this.templateManagerModal.templateCode = template.code;
  }

  useCurrentCode(): void {
    this.templateManagerModal.templateCode = this.contents;
    ui.success(T.graderCodeCopied);
  }

  clearForm(): void {
    this.templateManagerModal.selectedTemplate = null;
    this.templateManagerModal.templateName = '';
    this.templateManagerModal.templateCode = '';
    const input = this.$refs.templateNameInput as HTMLInputElement;
    if (input) {
      input.focus();
    }
  }

  async saveCurrentTemplate(): Promise<void> {
    const templateName = this.templateManagerModal.templateName.trim();
    const templateCode = this.templateManagerModal.templateCode.trim();

    if (!templateName) {
      ui.error(T.graderTemplateNameRequired);
      return;
    }

    if (!templateCode) {
      ui.error(T.graderCannotSaveEmptyTemplate);
      return;
    }

    if (templateName.length > 100) {
      ui.error(T.graderTemplateNameTooLong);
      return;
    }

    try {
      await api.CodeTemplate.create({
        language: this.language,
        template_name: templateName,
        code: templateCode,
      });
      ui.success(T.graderTemplateSaved);
      await this.loadTemplates();

      // Clear form after save
      this.clearForm();
    } catch (error) {
      ui.error((error as Error).message || T.graderTemplateSaveFailed);
    }
  }

  async deleteCurrentTemplate(): Promise<void> {
    if (!this.templateManagerModal.selectedTemplate) return;

    if (!confirm(T.graderConfirmDeleteTemplate)) {
      return;
    }

    try {
      await api.CodeTemplate.delete({
        template_id: this.templateManagerModal.selectedTemplate.template_id,
      });
      ui.success(T.graderTemplateDeleted);
      await this.loadTemplates();

      // Clear form after delete
      this.clearForm();
    } catch (error) {
      ui.error((error as Error).message || T.graderTemplateDeleteFailed);
    }
  }
}
</script>

<style lang="scss" scoped>
@import '../../../sass/main.scss';

.editor-toolbar {
  background: var(--monaco-editor-toolbar-background-color);
  border-bottom: 1px solid var(--monaco-editor-toolbar-border-bottom-color);
}

.editor-toolbar label {
  font-size: 12px;
  background: var(--monaco-editor-toolbar-label-background-color);
  color: var(--monaco-editor-toolbar-label-color);
  border: 1px solid var(--monaco-editor-toolbar-label-border-color);
}

.editor-toolbar select {
  font-size: 10px;
  background-color: var(--monaco-editor-toolbar-label-background-color);
  color: var(--monaco-editor-toolbar-label-color);
}

.template-btn {
  font-size: 12px;
  padding: 2px 6px;
}

.editor {
  border: 1px solid var(--monaco-editor-toolbar-label-border-color);
}

/* Dark theme styles */
.vs-dark .editor-toolbar {
  background: var(--vs-dark-background-color);
}

.vs-dark .editor-toolbar label {
  background: var(--vs-dark-background-color);
  color: var(--vs-dark-font-color);
}

.vs-dark .editor-toolbar select {
  background-color: var(--vs-dark-background-color);
  color: var(--vs-dark-font-color);
}

.vs-dark .editor {
  border: 1px solid var(--vs-dark-font-color);
}

/* Modal Styling */
.modal {
  background-color: rgba(0, 0, 0, 0.5);
}

.modal-header {
  background-color: var(--header-primary-color);
  color: white;
  border-bottom: none;
}

.modal-header .close {
  color: white;
  opacity: 0.8;
  text-shadow: none;
}

.modal-header .close:hover {
  opacity: 1;
}

.modal-body {
  max-height: 70vh;
  overflow-y: auto;
}

.modal-footer {
  background-color: #f8f9fa;
  border-top: 1px solid #dee2e6;
}

.section-title {
  font-weight: 600;
  color: #495057;
  margin-bottom: 0.75rem;
  padding-bottom: 0.5rem;
  border-bottom: 2px solid #dee2e6;
}

.template-list {
  max-height: 180px;
  overflow-y: auto;
}

.template-list .list-group-item {
  cursor: pointer;
}

.template-list .list-group-item.active {
  background-color: var(--header-primary-color);
  border-color: var(--header-primary-color);
}

.template-list .list-group-item.active .text-muted {
  color: rgba(255, 255, 255, 0.8) !important;
}

.template-form .form-group label {
  font-weight: 600;
  color: #495057;
}

.code-textarea {
  font-family: 'Consolas', 'Monaco', 'Courier New', monospace;
  font-size: 13px;
  resize: vertical;
}

.save-btn {
  color: #fff !important;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 0.25rem;
}
</style>
