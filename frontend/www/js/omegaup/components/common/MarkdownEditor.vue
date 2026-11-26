<template>
  <div class="markdown-editor">
    <div v-if="!readOnly" class="d-flex justify-content-end mb-1">
      <div class="btn-group btn-group-sm" role="group">
        <button
          type="button"
          class="btn"
          :class="editMode ? 'btn-primary' : 'btn-outline-secondary'"
          data-markdown-editor-edit
          @click="editMode = true"
        >
          {{ T.wordsEdit }}
        </button>
        <button
          type="button"
          class="btn"
          :class="!editMode ? 'btn-primary' : 'btn-outline-secondary'"
          data-markdown-editor-preview
          @click="editMode = false"
        >
          {{ T.wordsPreview }}
        </button>
      </div>
    </div>
    <div v-show="editMode">
      <div v-show="!readOnly" ref="buttonBar" class="wmd-button-bar"></div>
      <textarea
        ref="input"
        class="form-control wmd-input"
        :class="{ 'is-invalid': invalid }"
        :value="value"
        :rows="rows"
        :readonly="readOnly"
        :disabled="readOnly"
        :required="required"
        :maxlength="maxlength"
        v-bind="$attrs"
        @input="onInput"
        @keyup="onInput"
      ></textarea>
    </div>
    <omegaup-markdown
      v-if="!editMode"
      :markdown="value"
      class="wmd-preview border rounded p-2"
    ></omegaup-markdown>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop, Ref } from 'vue-property-decorator';
import T from '../../lang';
import * as Markdown from '@/third_party/js/pagedown/Markdown.Editor.js';
import * as markdownModule from '../../markdown';
import omegaup_Markdown from '../Markdown.vue';

const markdownConverter = new markdownModule.Converter({ preview: true });

@Component({
  inheritAttrs: false,
  components: { 'omegaup-markdown': omegaup_Markdown },
})
export default class MarkdownEditor extends Vue {
  @Prop({ default: false }) invalid!: boolean;
  @Prop({ required: true }) value!: string;
  @Prop({ required: true }) idPostfix!: string;
  @Prop({ default: false }) readOnly!: boolean;
  @Prop({ default: false }) required!: boolean;
  @Prop({ default: null }) maxlength!: number | null;
  @Prop({ default: 5 }) rows!: number;

  @Ref() readonly buttonBar!: HTMLDivElement;
  @Ref() readonly input!: HTMLTextAreaElement;

  T = T;
  editMode = true;
  markdownEditor: Markdown.Editor | null = null;

  mounted() {
    if (this.readOnly) return;
    this.markdownEditor = new Markdown.Editor(
      markdownConverter.converter,
      this.idPostfix,
      {
        panels: {
          buttonBar: this.buttonBar,
          preview: null,
          input: this.input,
        },
      },
    );
    this.markdownEditor.run();
    this.removeImageButton();
    this.buttonBar.addEventListener('click', () => {
      this.$nextTick(() => this.onInput());
    });
  }

  removeImageButton(): void {
    const imageButton = this.buttonBar.querySelector(
      '[id^="wmd-image-button"]',
    );
    if (imageButton) {
      imageButton.remove();
    }
    this.input.addEventListener(
      'keydown',
      (event: KeyboardEvent) => {
        if (
          (event.ctrlKey || event.metaKey) &&
          event.key.toLowerCase() === 'g'
        ) {
          event.preventDefault();
          event.stopImmediatePropagation();
        }
      },
      true,
    );
  }

  onInput(): void {
    if (this.input.value !== this.value) {
      this.$emit('input', this.input.value);
    }
  }
}
</script>

<style lang="scss" scoped>
@import '../../../../sass/main.scss';
@import '../../../../third_party/js/pagedown/demo/browser/demo.css';

.wmd-button-bar {
  background-color: var(--wmd-button-bar-background-color);
}

.wmd-input {
  min-height: 120px;
  resize: vertical;
}

.wmd-preview {
  background-color: var(--wmd-button-bar-background-color, #f8f9fa);
  min-height: 120px;
  max-height: 250px;
  overflow-y: auto;
  font-size: 0.9rem;
}
</style>
