<template>
  <vue-mathjax v-bind:formula="html" v-bind:safe="false"></vue-mathjax>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import * as markdown from '../markdown';

import { VueMathjax } from 'vue-mathjax';

const markdownConverter = markdown.markdownConverter();

@Component({
  components: {
    'vue-mathjax': VueMathjax,
  },
})
export default class Markdown extends Vue {
  @Prop() markdown!: string;

  get html(): string {
    return markdownConverter.makeHtml(this.markdown);
  }
}
</script>
