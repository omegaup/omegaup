<template>
  <div></div>
</template>

<script lang="ts">
import { Vue, Component, Prop, Watch } from 'vue-property-decorator';
import { Store } from 'vuex';
import { State, StoreMapping } from './CaseSelectorTypescript.vue';
import * as Util from './util';
import * as monaco from 'monaco-editor';

@Component
export default class GraderMonacoDiff extends Vue {
  @Prop({ required: true }) store!: Store<State>;
  @Prop({ required: true }) storeMapping!: StoreMapping;
  @Prop({ default: 'vs-dark' }) theme!: string;
  @Prop({ default: false }) readOnly!: boolean;

  title = 'diff';

  get originalContents(): string {
    return Util.vuexGet(this.store, this.storeMapping.originalContents);
  }

  get modifiedContents(): string {
    return Util.vuexGet(this.store, this.storeMapping.modifiedContents);
  }

  @Watch('originalContents')
  onOriginalContentsChange(newValue: string): void {
    this._originalModel.setValue(newValue);
  }
  @Watch('modifiedContents')
  onModifiedContentsChange(newValue: string): void {
    this._modifiedModel.setValue(newValue);
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
