<template>
  <div class="root d-flex flex-row h-100">
    <textarea
      v-model="contents"
      class="col px-0"
      :class="theme"
      :disabled="readOnly"
    ></textarea>
  </div>
</template>

<script>
import * as Util from './util';

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
    extension: {
      type: String,
      required: true,
    },
    module: {
      type: String,
      default: null,
    },
    readOnly: {
      type: Boolean,
      default: false,
    },
    theme: {
      type: String,
      default: 'vs',
    },
  },
  computed: {
    filename: function () {
      if (typeof this.storeMapping.module !== 'undefined') {
        return (
          Util.vuexGet(this.store, this.storeMapping.module) +
          '.' +
          this.extension
        );
      }
      return this.module + '.' + this.extension;
    },
    contents: {
      get() {
        return Util.vuexGet(this.store, this.storeMapping.contents);
      },
      set(value) {
        if (this.readOnly) return;
        Util.vuexSet(this.store, this.storeMapping.contents, value);
      },
    },
    title: function () {
      return this.filename;
    },
  },
};
</script>

<style>
textarea.vs-dark {
  background: #222;
  border: 0px;
  font-family: 'Droid Sans Mono', 'Courier New', monospace,
    'Droid Sans Fallback';
  color: #d4d4d4;
}

textarea.vs {
  border: 0px;
  font-family: 'Droid Sans Mono', 'Courier New', monospace,
    'Droid Sans Fallback';
}
</style>
