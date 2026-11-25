<template>
  <div></div>
</template>

<script lang="ts">
import { Vue, Component, Prop, Watch } from 'vue-property-decorator';
import { Store } from 'vuex';
import { State, StoreMapping } from './CaseSelectorTypescript.vue';
import * as Util from './util';
import * as monaco from 'monaco-editor';

export const languageMonacoModelMapping: { [key: string]: string } = {
  cpp11: 'cpp',
  'cpp11-gcc': 'cpp',
  'cpp11-clang': 'cpp',
  'cpp17-gcc': 'cpp',
  'cpp17-clang': 'cpp',
  'cpp20-gcc': 'cpp',
  'cpp20-clang': 'cpp',
  cpp: 'cpp',
  cs: 'csharp',
  java: 'java',
  kt: 'kotlin',
  lua: 'lua',
  py: 'python',
  py2: 'python',
  py3: 'python',
  rb: 'ruby',
  go: 'go',
  rs: 'rust',
  js: 'javascript',

  // Fake languages.
  idl: 'text',
  in: 'text',
  out: 'text',
  err: 'text',
};

export const languageExtensionMapping: { [key: string]: string } = {
  cpp11: 'cpp',
  'cpp11-gcc': 'cpp',
  'cpp11-clang': 'cpp',
  'cpp17-gcc': 'cpp',
  'cpp17-clang': 'cpp',
  'cpp20-gcc': 'cpp',
  'cpp20-clang': 'cpp',
  cpp: 'cpp',
  cs: 'cs',
  java: 'java',
  kt: 'kt',
  lua: 'lua',
  py: 'py',
  py2: 'py',
  py3: 'py',
  rb: 'rb',
  go: 'go',
  rs: 'rs',
  js: 'js',

  // Fake languages.
  idl: 'idl',
  in: 'in',
  out: 'out',
  err: 'err',
};

@Component
export default class GraderMonacoEditor extends Vue {
  @Prop({ required: true }) store!: Store<State>;
  @Prop({ required: true }) storeMapping!: StoreMapping;
  @Prop({ default: 'vs-dark' }) theme!: string;
  @Prop({ default: null }) initialModule!: null | string;
  @Prop({ default: false }) readOnly!: boolean;
  @Prop({ default: null }) extension!: null | string;
  @Prop({ default: null }) initialLanguage!: null | string;

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
  set(value: string) {
    Util.vuexSet(this.store, this.storeMapping.contents, value);
  }

  get filename(): string {
    const extension = this.extension || languageExtensionMapping[this.language];
    return `${this.module}.${extension}`;
  }

  get title(): string {
    return this.filename;
  }

  get visible(): boolean {
    if (!this.storeMapping.visible) return true;
    return Util.vuexGet(this.store, this.storeMapping.visible);
  }

  @Watch('language')
  onLanguageChanged(newValue: string): void {
    monaco.editor.setModelLanguage(
      this._model,
      Util.languageMonacoModelMapping[newValue],
    );
  }

  @Watch('contents')
  onContentsChanged(newValue: string): void {
    if (this._model.getValue() == newValue) {
      return;
    }
    this._model.setValue(newValue);
  }

  mounted() {
    this._editor = monaco.editor.create(this.$el as HTMLElement, {
      autoIndent: 'full',
      formatOnPaste: true,
      formatOnType: true,
      language: Util.languageMonacoModelMapping[this.language],
      readOnly: this.readOnly,
      theme: this.theme,
      value: this.contents,
    });
    this._model = this._editor.getModel();
    this._model.onDidChangeContent(() => {
      this.contents = this._model.getValue();
    });
  }

  onResize() {
    this._editor.layout();
  }
}
</script>

<style scoped>
div {
  width: 100%;
  height: 100%;
}
</style>
