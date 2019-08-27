<template>
  <omegaup-arena-codemirror ref="cm-wrapper"
        v-bind:options="editorOptions"
        v-bind:value="value"
        v-on:change="$emit('change', value)"
        v-on:input="$emit('input', value)"></omegaup-arena-codemirror>
</template>

<script lang="ts">
import { Vue, Component, Prop, Watch } from 'vue-property-decorator';
import omegaup from '../../api.js';
import { T } from '../../omegaup.js';
import UI from '../../ui.js';
import { codemirror } from 'vue-codemirror-lite';

const languageModeMap: {
  [language: string]: string;
} = {
  c: 'text/x-csrc',
  cpp: 'text/x-c++src',
  java: 'text/x-java',
  py: 'text/x-python',
  rb: 'text/x-ruby',
  pl: 'text/x-perl',
  cs: 'text/x-csharp',
  pas: 'text/x-pascal',
  cat: 'text/plain',
  hs: 'text/x-haskell',
  cpp11: 'text/x-c++src',
  lua: 'text/x-lua',
};

// Preload all language modes.
const modeList: string[] = [
  'clike',
  'python',
  'ruby',
  'perl',
  'pascal',
  'haskell',
  'lua',
];

for (const mode of modeList) {
  require(`codemirror/mode/${mode}/${mode}.js`);
}

interface EditorOptions {
  tabSize: number;
  lineNumbers: boolean;
  mode?: string;
  readOnly: boolean;
}

@Component({
  components: {
    'omegaup-arena-codemirror': codemirror,
  },
})
export default class ArenaCodeView extends Vue {
  @Prop() language!: string;
  @Prop() readonly!: boolean;
  @Prop() value!: string;

  T = T;
  mode?: string = languageModeMap[this.language];

  data(): { [key: string]: any } {
    return {
      mode: languageModeMap[this.language],
    };
  }

  get editorOptions(): EditorOptions {
    return {
      tabSize: 2,
      lineNumbers: true,
      mode: this.mode,
      readOnly: this.readonly,
    };
  }

  @Watch('language')
  onLanguageChange(newLanguage: string) {
    this.mode = languageModeMap[newLanguage];
  }
}

</script>
