<template>
  <omegaup-arena-codemirror ref="cm-wrapper"
        v-bind:options="editorOptions"
        v-bind:value="value"
        v-on:change="onChange"
        v-on:input="onInput"></omegaup-arena-codemirror>
</template>

<script>
import {T, API} from '../../omegaup.js';
import UI from '../../ui.js';
import {codemirror} from 'vue-codemirror';

const languageModeMap = {
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

// Preload all language modes.
const modeList =
    ['clike', 'python', 'ruby', 'perl', 'pascal', 'haskell', 'lua'];
for (const mode of modeList) {
  require('codemirror/mode/' + mode + '/' + mode + '.js');
}

export default {
  props: {
    language: String,
    readOnly: Boolean,
    value: String,
  },
  data: function() {
    return {
      editorOptions: {
        tabSize: 2, lineNumbers: true, mode: languageModeMap[this.language],
            readOnly: this.readOnly
      }
    }
  },
  methods: {
    onChange: function(value) { this.$emit('change', value);},
    onInput: function(value) { this.$emit('input', value);},
  },
  watch: {
    language: function(newLanguage) {
      this.editorOptions.mode = languageModeMap[newLanguage];
    }
  },
  components: {
    "omegaup-arena-codemirror": codemirror,
  }
};

</script>
