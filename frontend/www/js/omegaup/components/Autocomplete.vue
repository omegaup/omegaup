<template>
  <input class="typeahead form-control"
        ref="input">
</template>

<script>
import {UI} from '../omegaup.js';
export default {
  props: {
      init: Function
  },
  data: function() {
    return { value: "" }
  },
  mounted: function() {
      this.init($(this.$el));
  },
  methods: {updateInput() { this.$emit('input', this.$refs.input.value);}}
}
</script>
