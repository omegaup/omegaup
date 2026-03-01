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

  autoDetectActive: boolean = true;
  isDetecting: boolean = false;
  _detectTimeout: ReturnType<typeof setTimeout> | null = null;
  isManualLanguageChange: boolean = false;

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
      monaco.editor.setModelLanguage(
        this._model,
        Util.supportedLanguages[value].modelMapping,
      );
    }
    window.dispatchEvent(new Event('grader:language-detect-clear'));
    this.isManualLanguageChange = false;
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

  created(): void {
    try {
      const pref = localStorage.getItem('grader:autoDetectLanguage');
      this.autoDetectActive = pref !== 'false';
    } catch (e) {
      // ignore
    }
    window.addEventListener(
      'grader:auto-detect-preference',
      this.onAutoDetectPreference as any,
    );
    window.addEventListener(
      'trigger-auto-detect',
      this.detectLanguageImmediate as any,
    );
    window.addEventListener(
      'grader:manual-language-change',
      this.onManualLanguageChange as any,
    );
  }

  mounted(): void {
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

    this._model.onDidChangeContent((e) => {
      store.dispatch(this.storeMapping.contents, this._model?.getValue() || '');
      if (this._detectTimeout) clearTimeout(this._detectTimeout);
      const isPaste =
        e.changes.length > 1 || e.changes.some((c) => c.text.length > 20);
      if (isPaste) {
        setTimeout(() => this.detectLanguageImmediate(), 100);
      } else {
        this._detectTimeout = setTimeout(
          () => this.detectLanguageImmediate(),
          2000,
        );
      }
    });

    window.addEventListener('resize', this.onResize);
    this.onResize();
  }

  unmounted(): void {
    window.removeEventListener(
      'code-and-language-set',
      this.onCodeAndLanguageSet,
    );
    window.removeEventListener(
      'grader:auto-detect-preference',
      this.onAutoDetectPreference as any,
    );
    window.removeEventListener(
      'trigger-auto-detect',
      this.detectLanguageImmediate as any,
    );
    window.removeEventListener(
      'grader:manual-language-change',
      this.onManualLanguageChange as any,
    );
    window.removeEventListener('resize', this.onResize);
    if (this._detectTimeout) clearTimeout(this._detectTimeout);
  }

  onManualLanguageChange(): void {
    this.isManualLanguageChange = true;

    if (this._detectTimeout) {
      clearTimeout(this._detectTimeout);
      this._detectTimeout = null;
    }
    window.dispatchEvent(new Event('grader:language-detect-clear'));
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

  detectLanguageImmediate(): void {
    if (
      this.isDetecting ||
      !this.autoDetectActive ||
      this.isManualLanguageChange
    ) {
      this.isManualLanguageChange = false;
      return;
    }
    const code = this._model?.getValue() || this.contents || '';
    if (!code || code.trim().length < 20) {
      window.dispatchEvent(new Event('grader:language-detect-clear'));
      return;
    }

    this.isDetecting = true;
    try {
      const detected = Util.detectLanguageFromCode(code);
      if (
        detected &&
        detected.language &&
        detected.language !== this.language
      ) {
        const currentFamily = Util.LANGUAGE_FAMILIES[this.language];
        const detectedFamily = Util.LANGUAGE_FAMILIES[detected.language];
        if (
          currentFamily &&
          detectedFamily &&
          currentFamily === detectedFamily
        ) {
          window.dispatchEvent(new Event('grader:language-detect-clear'));
          return;
        }
        window.dispatchEvent(
          new CustomEvent('grader:language-detected', { detail: detected }),
        );
      } else {
        window.dispatchEvent(new Event('grader:language-detect-clear'));
      }
    } finally {
      this.isDetecting = false;
    }
  }

  onAutoDetectPreference(e: CustomEvent): void {
    this.autoDetectActive = Boolean(e.detail);
    if (!this.autoDetectActive) {
      window.dispatchEvent(new Event('grader:language-detect-clear'));
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
