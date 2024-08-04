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
export default class DiffEditor extends Vue {
  @Prop({ required: true }) storeMapping!: any;
  @Prop({ default: 'vs-dark' }) theme!: string;
  @Prop({ default: false }) readOnly!: boolean;

  _originalModel: monaco.editor.ITextModel | null = null;
  _modifiedModel: monaco.editor.ITextModel | null = null;
  _editor: monaco.editor.IStandaloneDiffEditor | null = null;

  get originalContents(): string {
    return Util.vuexGet(store, this.storeMapping.originalContents);
  }

  get modifiedContents(): string {
    return Util.vuexGet(store, this.storeMapping.modifiedContents);
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

    this._editor = monaco.editor.createDiffEditor(this.$el as HTMLElement, {
      theme: this.theme,
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
