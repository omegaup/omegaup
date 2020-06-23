<template>
  <vue-mathjax v-bind:formula="html" v-bind:safe="false"></vue-mathjax>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import * as markdown from '../markdown';
import * as MarkdownConverter from '@/third_party/js/pagedown/Markdown.Converter.js';

import { VueMathjax } from 'vue-mathjax';

const markdownConverter = markdown.markdownConverter();

@Component({
  components: {
    'vue-mathjax': VueMathjax,
  },
})
export default class Markdown extends Vue {
  @Prop() markdown!: string;
  @Prop({ default: null }) imageMapping!: MarkdownConverter.ImageMapping | null;
  @Prop({ default: null }) problemSettings!: markdown.ProblemSettings | null;

  get html(): string {
    if (this.problemSettings || this.imageMapping) {
      return markdownConverter.makeHtmlWithImages(
        this.markdown,
        this.imageMapping || {},
        this.problemSettings || {},
      );
    }
    return markdownConverter.makeHtml(this.markdown);
  }
}
</script>
