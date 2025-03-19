<template>
  <div>
    <!-- Font Size Dropdown -->
    <label for="font-size">Font Size: </label>
    <select id="font-size" v-model="selectedFontSize" @change="onFontSizeChange">
      <option v-for="size in fontSizes" :key="size" :value="size">{{ size }}px</option>
    </select>
    <!-- Editor Container -->
    <div ref="editorContainer" style="width: 100%; height: 100%;"></div>
  </div>
</template>

// TODO: replace all instances of any with correct type
<script lang="ts">
import { Vue, Component, Prop, Watch } from 'vue-property-decorator';
import store from './GraderStore';
import * as Util from './util';
import * as monaco from 'monaco-editor';

@Component
export default class MonacoEditor extends Vue {
  // TODO: place more restrictions on value of keys inside storeMapping
  @Prop({ required: true }) storeMapping!: { [key: string]: string };
  @Prop({ default: false }) readOnly!: boolean;

  _editor: monaco.editor.IStandaloneCodeEditor | null = null;
  _model: monaco.editor.ITextModel | null = null;

  // default font size
  selectedFontSize: number = 14;
  fontSizes: number[] = [12, 14, 16, 18, 20, 22, 24, 26];

  get theme(): string {
    return store.getters['theme'];
  }

  get language(): string {
    return store.getters[this.storeMapping.language];
  }

  get contents(): string {
    return store.getters[this.storeMapping.contents];
  }

  set contents(value: string) {
    store.dispatch(this.storeMapping.contents, value);
  }

  @Watch('theme')
  onThemeChange(value: string): void {
    if (this._editor) {
      this._editor.updateOptions({ theme: value });
    }
  }

  mounted(): void {
    window.addEventListener('code-and-language-set', this.onCodeAndLanguageSet);

    this._editor = monaco.editor.create(this.$refs.editorContainer as HTMLElement, {
      autoIndent: 'brackets',
      formatOnPaste: true,
      formatOnType: true,
      language: Util.supportedLanguages[this.language].modelMapping,
      readOnly: this.readOnly,
      theme: this.theme,
      value: this.contents,
      fontSize: this.selectedFontSize,
    });

    this._model = this._editor.getModel();
    if (!this._model) return;

    this._model.onDidChangeContent(() => {
      store.dispatch(this.storeMapping.contents, this._model?.getValue() || '');
    });

    window.addEventListener('resize', this.onResize);
    this.onResize();
  }

  onFontSizeChange(): void {
    if (this._editor) {
      this._editor.updateOptions({ fontSize: this.selectedFontSize });
    }
  }

  unmounted(): void {
    window.removeEventListener('code-and-language-set', this.onCodeAndLanguageSet);
    window.removeEventListener('resize', this.onResize);
  }

  onResize(): void {
    // scaling does not work as intended
    // the cursor does not click where it's supposed to
    // this is an alternative solution to zooming in/out
    if (this._editor) {
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
