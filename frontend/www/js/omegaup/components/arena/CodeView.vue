<template>
  <div data-code-mirror>
    <codemirror-editor
      ref="cm-wrapper"
      :options="editorOptions"
      :value="value"
      @change="onChange"
      @input="onInput"
    ></codemirror-editor>
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import T from '../../lang';
import { codemirror } from 'vue-codemirror-lite';

export const languageModeMap: {
  [language: string]: string;
} = {
  c: 'text/x-csrc',
  'c11-gcc': 'text/x-csrc',
  'c11-clang': 'text/x-csrc',
  cpp: 'text/x-c++src',
  'cpp17-gcc': 'text/x-c++src',
  'cpp17-clang': 'text/x-c++src',
  'cpp20-gcc': 'text/x-c++src',
  'cpp20-clang': 'text/x-c++src',
  java: 'text/x-java',
  kt: 'text/x-kotlin',
  py: 'text/x-python',
  py2: 'text/x-python',
  py3: 'text/x-python',
  rb: 'text/x-ruby',
  pl: 'text/x-perl',
  cs: 'text/x-csharp',
  pas: 'text/x-pascal',
  cat: 'text/plain',
  hs: 'text/x-haskell',
  cpp11: 'text/x-c++src',
  'cpp11-gcc': 'text/x-c++src',
  'cpp11-clang': 'text/x-c++src',
  lua: 'text/x-lua',
  go: 'text/x-go',
  rs: 'text/x-rust',
  js: 'text/x-javascript',
};

// Preload all language modes.
export const modeList: string[] = [
  'clike',
  'python',
  'ruby',
  'perl',
  'pascal',
  'haskell',
  'lua',
  'go',
  'rust',
  'javascript',
];

for (const mode of modeList) {
  require(`codemirror/mode/${mode}/${mode}.js`);
}

export interface EditorOptions {
  tabSize: number;
  lineNumbers: boolean;
  mode?: string;
  readOnly: boolean;
  gutters?: string[];
  viewportMargin?: number;
}

export default defineComponent({
  name: 'CodeView',
  components: {
    'codemirror-editor': codemirror,
  },
  props: {
    language: {
      type: String,
      default: 'cpp17-gcc',
    },
    readonly: {
      type: Boolean,
      default: false,
    },
    value: {
      type: String,
      default: '',
    },
  },
  emits: ['change', 'input', 'change-language'],
  data() {
    return {
      T,
      mode: languageModeMap[this.language] || languageModeMap['cpp17-gcc'],
    };
  },
  computed: {
    editorOptions(): EditorOptions {
      return {
        tabSize: 2,
        lineNumbers: true,
        mode: this.mode,
        readOnly: this.readonly,
      };
    },
  },
  watch: {
    language(newLanguage: string): void {
      this.mode = languageModeMap[newLanguage];
    },
  },
  mounted() {
    this.refresh();
    const codeAndLanguage = {
      code: this.value,
      language: this.language,
    };
    window.dispatchEvent(
      new CustomEvent('code-and-language-set', { detail: codeAndLanguage }),
    );

    this.onInput(codeAndLanguage.code);
    this.onChange(codeAndLanguage.code);
    this.$emit('change-language', codeAndLanguage.language);
  },
  methods: {
    refresh(): void {
      // eslint-disable-next-line @typescript-eslint/ban-ts-comment
      // @ts-ignore vue-codemirror-lite does not declare `editor` as a legitimate
      // property, so TypeScript cannot know about it.
      // It's also possible for the actual editor to not have been set yet if
      // this method is used before the mounted event handler is called.
      (this.$refs['cm-wrapper'] as any)?.editor?.refresh();
    },
    onChange(value: string): void {
      this.$emit('change', value);
    },
    onInput(value: string): void {
      this.$emit('input', value);
    },
  },
});
</script>

<style lang="scss">
@import '../../../../sass/main.scss';

[data-code-mirror] {
  height: 100%;

  .vue-codemirror-wrap {
    height: 95%;

    .CodeMirror {
      height: 100%;

      .CodeMirror-scroll {
        height: 226px;
      }
    }
  }
}
</style>
