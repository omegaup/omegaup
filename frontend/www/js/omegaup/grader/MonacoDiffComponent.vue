<template>
  <div></div>
</template>

<script>
import * as Util from './util';
import * as monaco from 'monaco-editor';

export default {
  props: {
    store: Object,
    storeMapping: Object,
    theme: {
      type: String,
      'default': 'vs-dark',
    },
  },
  data: function() {
    return {
      title: 'diff',
    };
  },
  mounted: function() {
    this._originalModel =
        monaco.editor.createModel(this.originalContents, 'text/plain');
    this._modifiedModel =
        monaco.editor.createModel(this.modifiedContents, 'text/plain');

    this._editor = monaco.editor.createDiffEditor(this.$el, {
      theme: this.theme,
      readOnly: this.readOnly,
    });
    this._editor.setModel({
      original: this._originalModel,
      modified: this._modifiedModel,
    });
  },
  methods: {
    onResize: function() { this._editor.layout();},
  },
  computed: {
    originalContents() {
      return Util.vuexGet(this.store, this.storeMapping.originalContents);
    },
    modifiedContents() {
      return Util.vuexGet(this.store, this.storeMapping.modifiedContents);
    },
  },
  watch: {
    originalContents: function(value) { this._originalModel.setValue(value);},
    modifiedContents: function(value) { this._modifiedModel.setValue(value);},
  },
};
</script>

<style scoped>
div {
  width: 100%;
  height: 100%;
}
</style>
