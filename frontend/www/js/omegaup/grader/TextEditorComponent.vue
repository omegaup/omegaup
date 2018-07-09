<template>
  <div class="root d-flex flex-row h-100">
    <textarea class="col px-0"
         v-bind:disabled="readOnly"
         v-model="contents"></textarea>
  </div>
</template>

<script>
import * as Util from './util';

export default {
  props: {
    store: Object,
    storeMapping: Object,
    extension: String,
    module: {
      type: String,
      'default': null,
    },
    readOnly: {
      type: Boolean,
      'default': false,
    },
  },
  computed: {
    filename: function() {
      if (typeof(this.storeMapping.module) !== 'undefined') {
        return Util.vuexGet(this.store, this.storeMapping.module) + '.' +
               this.extension;
      }
      return this.module + '.' + this.extension;
    },
    contents: {
      get() { return Util.vuexGet(this.store, this.storeMapping.contents);},
      set(value) {
        if (this.readOnly) return;
        Util.vuexSet(this.store, this.storeMapping.contents, value);
      },
    },
    title: function() { return this.filename;},
  },
};
</script>

<style scoped>
textarea {
	background: #222222;
	border: 0px;
	font-family: "Droid Sans Mono", "Courier New", monospace, "Droid Sans Fallback";
	color: #d4d4d4;
}
</style>
