<template>
  <input class="typeahead form-control"
        ref="input">
</template>

<script>
import {UI} from '../omegaup.js';
export default {
  data: function() {
    return { value: "" }
  },
  mounted: function() { UI.problemTypeahead($(this.$el));},
  methods: {updateInput() { this.$emit('input', this.$refs.input.value);}}
}
</script>
