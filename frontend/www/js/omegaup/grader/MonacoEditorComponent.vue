<template>
  <div></div>
</template>

<script>
import * as Util from './util';
import * as monaco from 'monaco-editor';
import eventBus from './eventBus';
export default {
  props: {
    store: {
      type: Object,
      required: true,
    },
    storeMapping: {
      type: Object,
      required: true,
    },
    theme: {
      type: String,
      default: 'vs-dark',
    },
    initialModule: {
      type: String,
      default: null,
    },
    readOnly: {
      type: Boolean,
      default: false,
    },
    extension: {
      type: String,
      default: null,
    },
    initialLanguage: {
      type: String,
      default: null,
    },
  },
  computed: {
    language: function () {
      if (this.initialLanguage) return this.initialLanguage;
      return Util.vuexGet(this.store, this.storeMapping.language);
    },
    module: function () {
      if (this.initialModule) return this.initialModule;
      return Util.vuexGet(this.store, this.storeMapping.module);
    },
    contents: {
      get() {
        return Util.vuexGet(this.store, this.storeMapping.contents);
      },
      set(value) {
        Util.vuexSet(this.store, this.storeMapping.contents, value);
      },
    },
    filename: function () {
      return (
        this.module +
        '.' +
        (this.extension || Util.languageExtensionMapping[this.language])
      );
    },
    title: function () {
      return this.filename;
    },
    visible: function () {
      if (!this.storeMapping.visible) return true;
      return Util.vuexGet(this.store, this.storeMapping.visible);
    },
  },
  watch: {
    language: function (value) {
      monaco.editor.setModelLanguage(
        this._model,
        Util.languageMonacoModelMapping[value],
      );
    },
    contents: function (value) {
      if (this._model.getValue() != value) this._model.setValue(value);
    },
  },
  mounted: function () {
    // update modal component code and language here
    // eventBus.$on('modal-mount', (modalComponent) => {
    //
    // });
    this._editor = monaco.editor.create(this.$el, {
      autoIndent: true,
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
  },
  unmounted: function () {
    eventBus.$off('modal-mount');
  },
  methods: {
    onResize: function () {
      this._editor.layout();
    },
  },
};
</script>

<style scoped>
div {
  width: 100%;
  height: 100%;
}
</style>
