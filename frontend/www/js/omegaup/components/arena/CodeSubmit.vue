<template>
  <div class="template-code-submit">
    <code-view v-bind:fragment="fragment"
         v-bind:key="index"
         v-bind:language="language"
         v-for="(fragment, index) in codeFragments"></code-view>
  </div>
</template>

<script>
import {T, API} from '../../omegaup.js';
import UI from '../../ui.js';
import arena_Code from './Code.vue';

export default {
  props: {
    // An array of code fragment objects. Each object
    // will be passed on to its own code Vue and is
    // mutable. This is to save on rebuilding the final
    // code on every edit event.
    codeFragments: Array,
    language: String,
  },
  components: {
    'code-view': arena_Code,
  }
};

</script>
