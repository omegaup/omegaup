<template>
  <div></div>
</template>

<script lang="ts">
import { Vue, Component, Prop, Watch } from 'vue-property-decorator';
import * as Util from '../grader/util';
import * as monaco from 'monaco-editor';

@Component
export default class DiffEditorComponent extends Vue {
  @Prop({ type: Object, required: true }) store!: any;
  @Prop({ type: Object, required: true }) storeMapping!: any;
  @Prop({ type: String, default: 'vs-dark' }) theme!: string;
  @Prop({ type: Boolean, default: false }) readOnly!: boolean;

  title: string = 'diff';
  _originalModel: monaco.editor.ITextModel | null = null;
  _modifiedModel: monaco.editor.ITextModel | null = null;
  _editor: monaco.editor.IStandaloneDiffEditor | null = null;

  get originalContents(): string {
    return Util.vuexGet(this.store, this.storeMapping.originalContents);
  }

  get modifiedContents(): string {
    return Util.vuexGet(this.store, this.storeMapping.modifiedContents);
  }

  @Watch('originalContents')
  onOriginalContentsChange(value: string) {
    if (this._originalModel) {
      this._originalModel.setValue(value);
    }
  }

  @Watch('modifiedContents')
  onModifiedContentsChange(value: string) {
    if (this._modifiedModel) {
      this._modifiedModel.setValue(value);
    }
  }

  mounted() {
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

  onResize() {
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
