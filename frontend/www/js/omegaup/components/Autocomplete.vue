<template>
  <input class="typeahead form-control"
        ref="input"
        v-on:change="updateInput">
</template>

<script>
import {UI} from '../omegaup.js';

export default {
  props: {
    value: String,
    init: Function,
  },
  mounted: function() { this.init($(this.$el));},
  methods: {
    updateInput() { this.$emit('input', this.$refs.input.value);},
  },
};
</script>
