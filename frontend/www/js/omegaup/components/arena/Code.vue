<template>
  <!-- TODO: Use a code editor for both -->
  <pre v-if="fragment.readOnly">{{ fragment.contents }}</pre>
  <textarea v-else=""
        v-model="fragment.contents"></textarea>
</template>

<script>
import {T, API} from '../../omegaup.js';
import UI from '../../ui.js';

export default {
  props: {
    // Object for mutable access
    fragment: Object,
    language: String,
  },
};
</script>
