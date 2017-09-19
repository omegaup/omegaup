<template>
  <div class="template-code-submit">
    <omegaup-arena-code-view v-bind:fragment="fragment"
         v-bind:key="index"
         v-bind:language="language"
         v-for="(fragment, index) in codeFragments"></omegaup-arena-code-view>
  </div>
</template>

<script>
import {T, API} from '../../omegaup.js';
import UI from '../../ui.js';
import arena_CodeView from './CodeView.vue';

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
    'omegaup-arena-code-view': arena_CodeView,
  }
};

</script>
