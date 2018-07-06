<template>
  <input class="typeahead form-control"
        ref="input">
</template>

<script>
import {UI, API} from '../omegaup.js';
export default {
  data: function() {
    return { value: "" }
  },
  mounted: function() { UI.typeahead($(this.$el), API.Group.list);},
  methods: {updateInput() { this.$emit('input', this.$refs.input.value);}}
}
</script>
