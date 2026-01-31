<template>
  <div
    ref="root"
    data-markdown-statement
    :html="html"
    :class="{ 'full-width': fullWidth }"
  ></div>
</template>

<script lang="ts">
import { Vue, Component, Prop, Ref, Watch } from 'vue-property-decorator';
import * as markdown from '../markdown';

declare global {
  interface Window {
    MathJax?: {
      tex: any;
      startup: {
        typeset: boolean;
        elements?: HTMLElement[];
        ready: () => void;
        defaultReady?: () => void;
      };
      typeset?: (elements?: HTMLElement[]) => void;
      options: any;
      loader: any;
    };
  }
}

@Component
export default class Markdown extends Vue {
  @Prop() markdown!: string;
  @Ref() root!: HTMLElement;
  @Prop({ default: false }) fullWidth!: boolean;

  markdownConverter = new markdown.Converter();

  get html(): string {
    return this.markdownConverter.makeHtml(this.markdown);
  }

  mounted(): void {
    this.root.innerHTML = this.html;
  }

  @Watch('markdown')
  onMarkdownChanged(): void {
    this.root.innerHTML = this.html;
  }
}
</script>

<style lang="scss">
// This file cannot use `scoped` because it injects elements into the DOM at
// runtime, and is incompatible with how webpacked Vue creates scoped rules: by
// adding a compile-time random prefix to all classes.
//
// Instead, all the rules in this file are added as children of the root
// element, which has the `data-markdown-statement` data attribute.
@import '../../../../../node_modules/prismjs/themes/prism.css';
@import '../../../sass/main.scss';

[data-markdown-statement] {
  display: block;
  max-width: 50em;
  margin: 0 auto;

  &.full-width {
    max-width: 100%;
  }

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
    margin-bottom: 1em;
  }

  ul {
    list-style: disc;
  }

  ol {
    list-style: decimal;
  }

  ul li,
  ol li {
    margin-left: 0.25em;
  }

  pre {
    padding: 16px;
    background: var(--markdown-pre-background-color);
    margin: 1em 0;
    border-radius: 6px;
    display: block;
    line-height: 125%;
  }

  & > pre > button {
    margin-right: -16px;
    margin-top: -16px;
    padding: 6px;
    font-size: 90%;
  }

  td > button.clipboard {
    float: right;
    border-color: var(--markdown-button-clipboard-border-color);
    margin-left: 0.5em;
    margin-right: -6px;
    margin-top: -6px;
    padding: 3px;
    font-size: 90%;
  }

  figure {
    text-align: center;
    page-break-inside: avoid;
  }

  details {
    padding: 16px;
    border: 1px solid var(--markdown-details-border-color);

    summary {
      color: var(--markdown-details-summary-font-color);
    }

    &[open] > summary {
      margin-bottom: 24px;
    }
  }

  table td {
    border: 1px solid var(--markdown-td-border-color);
    padding: 10px;
  }

  table th {
    text-align: center;
  }

  table.sample_io {
    margin: 5px;
    padding: 5px;

    tbody {
      background: var(--markdown-sample-io-tbody-background-color);
      border: 1px solid var(--markdown-sample-io-tbody-border-color);

      tr:nth-child(even) {
        background: var(--markdown-sample-io-tr-even-element-background-color);
      }
    }

    th {
      padding: 10px;
      border-top: 0;
    }

    td {
      vertical-align: top;
      padding: 10px;
      border: 1px solid var(--markdown-sample-io-td-border-color);
    }

    pre {
      white-space: pre;
      word-break: keep-all;
      word-wrap: normal;
      background: transparent;
      border: 0;
      padding: 0;
      margin: inherit;
      max-width: 800px;
      overflow-x: auto;

      & > button {
        margin-left: 2em;
        padding: 3px;
        font-size: 80%;
      }
    }

    tr td:first-child pre {
      margin-right: 31px;
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

  code.libinteractive-download,
  code.output-only-download {
    background: var(--markdown-libinteractive-download-background-color);
    color: var(--markdown-libinteractive-download-font-color);
    margin: 1em 0;
    border: 1px dotted var(--markdown-libinteractive-download-border-color);
    display: block;
    text-align: center;
    font-size: 2em;
    line-height: 3em;
    min-height: 3em;
  }

  img {
    max-width: 100%;
    page-break-inside: avoid;
  }
}
</style>
