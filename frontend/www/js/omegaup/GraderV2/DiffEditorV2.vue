<template>
  <div ref="wrapper" class="diff-editor-wrapper">
    <div class="diff-header">
      <div class="header-section">
        <span class="header-label">{{ T.graderDiffEditorOriginal }}</span>
      </div>
      <div class="header-divider"></div>
      <div class="header-section">
        <span class="header-label">{{ T.graderDiffEditorModified }}</span>
        <span v-if="readOnly" class="readonly-badge">{{ T.graderDiffEditorReadOnly }}</span>
      </div>
    </div>
    <div ref="editorContainer" class="diff-editor-container"></div>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop, Watch } from 'vue-property-decorator';
import store from '../grader/GraderStore';
import * as monaco from 'monaco-editor';
import T from '../lang';

@Component
export default class DiffEditorV2 extends Vue {
  @Prop({ required: true }) storeMapping!: {
    originalContents: string;
    modifiedContents: string;
  };
  @Prop({ default: false }) readOnly!: boolean;
  @Prop({ default: 'text/plain' }) language!: string;

  T = T;
  private _originalModel: monaco.editor.ITextModel | null = null;
  private _modifiedModel: monaco.editor.ITextModel | null = null;
  private _editor: monaco.editor.IStandaloneDiffEditor | null = null;
  private _resizeObserver: ResizeObserver | null = null;

  get theme(): string {
    return store.getters['theme'] || 'vs-dark';
  }

  get originalContents(): string {
    return store.getters[this.storeMapping.originalContents] || '';
  }

  get modifiedContents(): string {
    return store.getters[this.storeMapping.modifiedContents] || '';
  }

  @Watch('originalContents')
  onOriginalContentsChange(value: string): void {
    if (this._originalModel && this._originalModel.getValue() !== value) {
      this._originalModel.setValue(value);
    }
  }

  @Watch('modifiedContents')
  onModifiedContentsChange(value: string): void {
    if (this._modifiedModel && this._modifiedModel.getValue() !== value) {
      this._modifiedModel.setValue(value);
    }
  }

  @Watch('theme')
  onThemeChange(newTheme: string): void {
    if (this._editor) monaco.editor.setTheme(newTheme);
  }

  mounted(): void {
    this._originalModel = monaco.editor.createModel(
      this.originalContents,
      this.language,
    );
    this._modifiedModel = monaco.editor.createModel(
      this.modifiedContents,
      this.language,
    );

    this._editor = monaco.editor.createDiffEditor(
      this.$refs.editorContainer as HTMLElement,
      {
        theme: this.theme,
        originalEditable: !this.readOnly,
        readOnly: this.readOnly,
        renderSideBySide: true,
        fontSize: 13,
        lineHeight: 20,
        fontFamily: "'JetBrains Mono', 'monospace'",
        scrollBeyondLastLine: false,
        minimap: { enabled: false },
        automaticLayout: true,
      },
    );

    this._editor.setModel({
      original: this._originalModel,
      modified: this._modifiedModel,
    });

    this._resizeObserver = new ResizeObserver(() => this.onResize());
    this._resizeObserver.observe(this.$refs.wrapper as HTMLElement);
  }

  beforeDestroy(): void {
    this._resizeObserver?.disconnect();
    this._editor?.dispose();
    this._originalModel?.dispose();
    this._modifiedModel?.dispose();
  }

  onResize(): void {
    this._editor?.layout();
  }
}
</script>

<style lang="scss" scoped>
.diff-editor-wrapper {
  display: flex;
  flex-direction: column;
  height: 100%;
  background: var(--diff-editor-wrapper-background-color);
}

.diff-header {
  display: flex;
  align-items: center;
  border-bottom: 1px solid var(--diff-editor-header-border-color);
  background: var(--diff-editor-header-background-color);
  min-height: 44px;
}

.header-section {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 10px 16px;
}

.header-label {
  font-size: 11px;
  font-weight: 600;
  color: var(--diff-editor-header-label-color);
  text-transform: uppercase;
}

.header-divider {
  width: 1px;
  height: 24px;
  background: var(--diff-editor-divider-background-color);
}

.readonly-badge {
  font-size: 11px;
  font-weight: 600;
  color: var(--diff-editor-readonly-badge-color);
  background: var(--diff-editor-readonly-badge-background-color);
  padding: 2px 8px;
  border-radius: 12px;
}

.diff-editor-container {
  flex: 1;
  min-height: 0;
}
</style>