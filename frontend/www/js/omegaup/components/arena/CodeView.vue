<template>
  <!-- TODO: Use a code editor for both -->
  <pre v-if="fragment.readOnly">
  {{ fragment.contents }}</pre><omegaup-arena-codemirror v-bind:options="editorOptions"
        v-else
        v-model="fragment.contents"></omegaup-arena-codemirror>
</template>

<script>
import {T, API} from '../../omegaup.js';
import UI from '../../ui.js';
import {codemirror} from 'vue-codemirror';

let languageModeMap = {
  'c': 'text/x-csrc',
  'cpp': 'text/x-c++src',
  'java': 'text/x-java',
  'py': 'text/x-python',
  'rb': 'text/x-ruby',
  'pl': 'text/x-perl',
  'cs': 'text/x-csharp',
  'pas': 'text/x-pascal',
  'cat': 'text/plain',
  'hs': 'text/x-haskell',
  'cpp11': 'text/x-c++src',
  'lua': 'text/x-lua',
};

export default {
  props: {
    // Object for mutable access
    fragment: Object,
    language: String,
  },
  data: function() {
    return {
      editorOptions: {
        tabSize: 2, lineNumbers: true, mode: languageModeMap[this.language],
      }
    }
  },
  watch: {
    language: function(newLanguage) {
      this.editorOptions.mode = languageModeMap[newLanguage];
    }
  },
  components: {codemirror: "omegaup-arena-codemirror"}
};
</script>
