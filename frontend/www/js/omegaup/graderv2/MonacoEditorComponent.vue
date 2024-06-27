<template>
  <div></div>
</template>

<script lang="ts">
import { Vue, Component, Prop, Watch } from 'vue-property-decorator';
import * as Util from '../grader/util';
import * as monaco from 'monaco-editor';

@Component
export default class MonacoEditorComponent extends Vue {
  @Prop({ type: Object, required: true }) store!: any;
  @Prop({ type: Object, required: true }) storeMapping!: any;
  @Prop({ type: String, default: 'vs-dark' }) theme!: string;
  @Prop({ type: String, default: null }) initialModule!: string | null;
  @Prop({ type: Boolean, default: false }) readOnly!: boolean;
  @Prop({ type: String, default: null }) extension!: string | null;
  @Prop({ type: String, default: null }) initialLanguage!: string | null;

  _editor: monaco.editor.IStandaloneCodeEditor | null = null;
  _model: monaco.editor.ITextModel | null = null;

  get language(): string {
    if (this.initialLanguage) return this.initialLanguage;
    return Util.vuexGet(this.store, this.storeMapping.language);
  }

  get module(): string {
    if (this.initialModule) return this.initialModule;
    return Util.vuexGet(this.store, this.storeMapping.module);
  }

  get contents(): string {
    return Util.vuexGet(this.store, this.storeMapping.contents);
  }
  set contents(value: string) {
    Util.vuexSet(this.store, this.storeMapping.contents, value);
  }

  get filename(): string {
    return (
      this.module +
      '.' +
      (this.extension || Util.supportedLanguages[this.language].extension)
    );
  }

  get title(): string {
    return this.filename;
  }

  get visible(): boolean {
    if (!this.storeMapping.visible) return true;
    return Util.vuexGet(this.store, this.storeMapping.visible);
  }

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

  mounted(): void {
    window.parent.addEventListener(
      'code-and-language-set',
      this.onCodeAndLanguageSet,
    );
    this._editor = monaco.editor.create(this.$el as HTMLElement, {
      autoIndent: 'brackets',
      formatOnPaste: true,
      formatOnType: true,
      language: Util.supportedLanguages[this.language].modelMapping,
      readOnly: this.readOnly,
      theme: this.theme,
      value: this.contents,
    });
    this._model = this._editor.getModel();
    if (!this._model) return;

    this._model.onDidChangeContent(() => {
      this.contents = this._model?.getValue() || '';
    });
  }

  unmounted(): void {
    window.parent.removeEventListener(
      'code-and-language-set',
      this.onCodeAndLanguageSet,
    );
  }

  onResize(): void {
    if (this._editor) {
      this._editor.layout();
    }
  }

  onCodeAndLanguageSet(e: any): void {
    e.detail.code = this.contents;
    e.detail.language = this.language;
  }
}
</script>

<style scoped>
div {
  width: 100%;
  height: 100%;
}
</style>
