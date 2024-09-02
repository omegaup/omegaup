<template>
  <div></div>
</template>

<script lang="ts">
// TODO: replace all instances of any with correct type
import { Vue, Component, Prop, Watch } from 'vue-property-decorator';
import store from './GraderStore';
import * as Util from './util';
import * as monaco from 'monaco-editor';

@Component
export default class MonacoEditor extends Vue {
  // TODO: place more restrictions on value of keys inside storeMapping
  @Prop({ required: true }) storeMapping!: {
    [key: string]: string;
  };
  @Prop({ default: 'vs-dark' }) theme!: string;
  @Prop({ default: false }) readOnly!: boolean;

  _editor: monaco.editor.IStandaloneCodeEditor | null = null;
  _model: monaco.editor.ITextModel | null = null;

  // default font size and line height
  readonly baseFontSize: number = 14;
  readonly baseLineHeight: number = 19;

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
    window.addEventListener('code-and-language-set', this.onCodeAndLanguageSet);

    this._editor = monaco.editor.create(
      this.$el as HTMLElement,
      {
        autoIndent: 'brackets',
        formatOnPaste: true,
        formatOnType: true,
        language: Util.supportedLanguages[this.language].modelMapping,
        readOnly: this.readOnly,
        theme: this.theme,
        value: this.contents,
      } as monaco.editor.IStandaloneEditorConstructionOptions,
    );
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

      this._editor.updateOptions({
        fontSize: this.baseFontSize * window.devicePixelRatio,
        lineHeight: this.baseLineHeight * window.devicePixelRatio,
      });
      this._editor.layout();
    }
  }

  onCodeAndLanguageSet(e: any) {
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
