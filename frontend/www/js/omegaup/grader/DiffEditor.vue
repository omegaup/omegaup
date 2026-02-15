<template>
  <div class="diff-editor-wrapper" :class="theme">
    <div class="diff-header">
      <div class="header-section">
        <span class="header-label">Original</span>
      </div>
      <div class="header-divider"></div>
      <div class="header-section">
        <span class="header-label">Modified</span>
        <span v-if="readOnly" class="readonly-badge">Read-only</span>
      </div>
    </div>
    <div ref="editorContainer" class="diff-editor-container"></div>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop, Watch } from 'vue-property-decorator';
import store from './GraderStore';
import * as monaco from 'monaco-editor';

@Component
export default class DiffEditor extends Vue {
  @Prop({ required: true }) storeMapping!: { [key: string]: string };
  @Prop({ default: false }) readOnly!: boolean;

  _originalModel: monaco.editor.ITextModel | null = null;
  _modifiedModel: monaco.editor.ITextModel | null = null;
  _editor: monaco.editor.IStandaloneDiffEditor | null = null;

  get theme(): string {
    return store.getters['theme'];
  }

  get originalContents(): string {
    return store.getters[this.storeMapping.originalContents];
  }

  get modifiedContents(): string {
    return store.getters[this.storeMapping.modifiedContents];
  }

  @Watch('originalContents')
  onOriginalContentsChange(value: string): void {
    if (this._originalModel) {
      this._originalModel.setValue(value);
    }
  }

  @Watch('modifiedContents')
  onModifiedContentsChange(value: string): void {
    if (this._modifiedModel) {
      this._modifiedModel.setValue(value);
    }
  }

  @Watch('theme')
  onThemeChange(newTheme: string): void {
    if (this._editor) {
      monaco.editor.setTheme(newTheme);
    }
  }

  mounted(): void {
    this._originalModel = monaco.editor.createModel(
      this.originalContents,
      'text/plain',
    );
    this._modifiedModel = monaco.editor.createModel(
      this.modifiedContents,
      'text/plain',
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
        fontFamily:
          "'JetBrains Mono', 'Fira Code', 'Monaco', 'Menlo', 'Courier New', monospace",
        scrollBeyondLastLine: false,
        minimap: { enabled: false },
      },
    );

    this._editor.setModel({
      original: this._originalModel,
      modified: this._modifiedModel,
    });
  }

  beforeDestroy(): void {
    if (this._editor) {
      this._editor.dispose();
    }
    if (this._originalModel) {
      this._originalModel.dispose();
    }
    if (this._modifiedModel) {
      this._modifiedModel.dispose();
    }
  }

  onResize(): void {
    if (this._editor) {
      this._editor.layout();
    }
  }
}
</script>

<style lang="scss" scoped>
.diff-editor-wrapper {
  display: flex;
  flex-direction: column;
  height: 100%;
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
  background: #fff;

  &.vs-dark {
    background: #1e1e1e;
  }
}

.diff-header {
  display: flex;
  align-items: center;
  border-bottom: 1px solid #e5e7eb;
  background: #f9fafb;
  min-height: 44px;

  .vs-dark & {
    background: #252525;
    border-bottom-color: #333;
  }
}

.header-section {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 10px 16px;
}

.header-label {
  font-size: 13px;
  font-weight: 600;
  color: #1a1a1a;

  .vs-dark & {
    color: #d4d4d4;
  }
}

.header-divider {
  width: 1px;
  height: 24px;
  background: #e5e7eb;

  .vs-dark & {
    background: #404040;
  }
}

.readonly-badge {
  font-size: 11px;
  font-weight: 600;
  padding: 3px 8px;
  border-radius: 4px;
  background: #fef3c7;
  color: #92400e;

  .vs-dark & {
    background: rgba(251, 191, 36, 0.15);
    color: #fbbf24;
  }
}

.diff-editor-container {
  flex: 1;
  overflow: hidden;
}
</style>
