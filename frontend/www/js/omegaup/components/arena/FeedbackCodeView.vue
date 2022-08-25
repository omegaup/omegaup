<template>
  <div data-feedback-code-mirror class="container-fluid">
    <div class="row">
      <div v-if="enableFeedback" class="gutter align-text-bottom">
        <div
          v-for="line in linesPerChunk"
          :key="line"
          class="linenumber"
          @mouseover="hover = line"
          @mouseleave="hover = null"
        >
          <button
            class="btn-xs text-weight-bold btn-primary"
            :hidden="hover != line"
            @click.prevent="onPressLine(line)"
          >
            +
          </button>
          {{ line }}
        </div>
      </div>
      <div class="code">
        <codemirror-editor
          ref="cm-wrapper"
          :options="editorOptions"
          :value="value"
          @change="onChange"
          @input="onInput"
        ></codemirror-editor>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop, Ref, Watch } from 'vue-property-decorator';
import T from '../../lang';
import { codemirror } from 'vue-codemirror-lite';
import { EditorOptions, languageModeMap, modeList } from './CodeView.vue';

for (const mode of modeList) {
  require(`codemirror/mode/${mode}/${mode}.js`);
}

@Component({
  components: {
    'codemirror-editor': codemirror,
  },
})
export default class CodeView extends Vue {
  @Prop() language!: string;
  @Prop({ default: false }) readonly!: boolean;
  @Prop() value!: string;
  @Prop({ default: () => [] }) linesPerChunk!: number[];
  @Prop({ default: false }) enableFeedback!: boolean;
  @Ref('cm-wrapper') readonly cmWrapper!: codemirror;

  T = T;
  mode = languageModeMap[this.language] || languageModeMap['cpp17-gcc'];
  hover: null | number = null;

  refresh() {
    // eslint-disable-next-line @typescript-eslint/ban-ts-comment
    // @ts-ignore vue-codemirror-lite does not declare `editor` as a legitimate
    // property, so TypeScript cannot know about it.
    // It's also possible for the actual editor to not have been set yet if
    // this method is used before the mounted event handler is called.
    this.cmWrapper.editor?.refresh();
  }

  get editorOptions(): EditorOptions {
    return {
      tabSize: 2,
      lineNumbers: false,
      mode: this.mode,
      readOnly: true,
    };
  }

  onChange(value: string): void {
    this.$emit('change', value);
  }

  onInput(value: string): void {
    this.$emit('input', value);
  }

  onPressLine(number: number) {
    this.$emit('show-feedback-form', number);
  }

  @Watch('hover')
  onHoverChange(line: null | number) {
    if (!line) {
      return;
    }
  }

  @Watch('language')
  onLanguageChange(newLanguage: string) {
    this.mode = languageModeMap[newLanguage];
  }
}
</script>

<style lang="scss">
@import '../../../../sass/main.scss';

[data-feedback-code-mirror] {
  height: auto;

  .vue-codemirror-wrap {
    height: 95%;

    .CodeMirror {
      height: auto;

      .CodeMirror-scroll {
        height: auto;
      }
    }
  }

  .gutter {
    width: 4%;
    background-color: var(--codemirror-gutter-background-color);
  }

  .code {
    width: 96%;
  }

  .linenumber {
    padding: 0 3px 0 5px;
    min-width: 20px;
    text-align: right;
    color: var(--codemirror-line-number-font-color);
    white-space: nowrap;
    cursor: pointer;
  }
}
</style>
