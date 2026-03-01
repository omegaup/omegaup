<template>
  <div></div>
</template>

<script lang="ts">
// TODO: replace all instances of any with correct type
import { Vue, Component, Prop, Watch } from 'vue-property-decorator';
import store from './GraderStore';
import * as monaco from 'monaco-editor';

@Component
export default class DiffEditor extends Vue {
  // TODO: place more restrictions on value of keys inside storeMapping
  @Prop({ required: true }) storeMapping!: {
    [key: string]: string;
  };
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

  mounted(): void {
    this._originalModel = monaco.editor.createModel(
      this.originalContents,
      'text/plain',
    );
    this._modifiedModel = monaco.editor.createModel(
      this.modifiedContents,
      'text/plain',
    );

    // both sides are either editable or not at the same time
    this._editor = monaco.editor.createDiffEditor(this.$el as HTMLElement, {
      theme: this.theme,
      originalEditable: !this.readOnly,
      readOnly: this.readOnly,
    });

    this._editor.setModel({
      original: this._originalModel,
      modified: this._modifiedModel,
    });
  }

  onResize(): void {
    if (this._editor) {
      this._editor.layout();
    }
  }
}
</script>

<style scoped>
div {
  width: 100%;
  height: 100%;
}
</style>
