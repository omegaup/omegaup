<template>
  <div class="statement">
    <vue-mathjax v-bind:formula="html" v-bind:safe="false"></vue-mathjax>
  </div>
</template>

<style lang="scss" scoped>
.statement {
  max-width: 50em;
  margin: 0 auto;

  h1 {
    font-size: 1.3em;
    margin: 1em 0 0.5em 0;
    font-weight: bold;
  }
  h2 {
    font-size: 1.1em;
    margin: 1em 0 0.5em 0;
    font-weight: bold;
  }
  h3 {
    font-size: 1em;
    margin: 1em 0 0.5em 0;
    font-weight: bold;
  }

  p,
  li {
    hyphens: auto;
    line-height: 150%;
    text-align: justify;
    orphans: 2;
    widows: 2;
    page-break-inside: avoid;
  }
  p {
    margin-bottom: 1.5em;
  }

  ul {
    list-style: disc;
  }
  ol {
    list-style: decimal;
  }
  ul li,
  ol li {
    margin-left: 2em;
  }

  pre {
    line-height: 125%;
  }

  figure {
    text-align: center;
    page-break-inside: avoid;
  }

  table td {
    border: 1px solid #000;
    padding: 10px;
  }
  table th {
    text-align: center;
  }
  table.sample_io {
    margin: 5px;
    padding: 5px;
    tbody {
      background: #eee;
      border: 1px solid #000;
    }
    th {
      padding: 10px;
      border-top: 0;
    }
    td {
      vertical-align: top;
      padding: 10px;
      border: 1px solid #000;
    }
    pre {
      white-space: pre;
      word-break: keep-all;
      word-wrap: normal;
      background: transparent;
      border: 0px;
      padding: 0px;
      margin: inherit;
    }
  }

  iframe {
    width: 100%;
    height: 400px;
  }

  a.libinteractive-help {
    display: inline-block;
    float: right;
  }

  img {
    max-width: 100%;
    page-break-inside: avoid;
  }
}
</style>

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
