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
    </div>
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

@Component
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
  onLanguageChange(value: string): void {
    if (this._model) {
      const languageInfo = Util.supportedLanguages[value];
      this._model.updateOptions({ tabSize: languageInfo.tabSize ?? 2 });
      monaco.editor.setModelLanguage(this._model, languageInfo.modelMapping);
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
      this._editor.updateOptions({
        theme: value,
      });
    }
  }

  mounted(): void {
    window.addEventListener('code-and-language-set', this.onCodeAndLanguageSet);

    const container = this.$refs.editorContainer as HTMLElement;
    if (!container) return;

    const languageInfo = Util.supportedLanguages[this.language];
    this._editor = monaco.editor.create(container, {
      autoIndent: 'brackets',
      tabSize: languageInfo.tabSize ?? 2,
      formatOnPaste: true,
      formatOnType: true,
      language: languageInfo.modelMapping,
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
  }

  unmounted(): void {
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
</style>
