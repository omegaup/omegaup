<template>
  <div data-markdown-statement ref="root" v-bind:html="html"></div>
</template>

<style lang="scss">
[data-markdown-statement] {
  display: block;
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

      tr:nth-child(even) {
        background: #f5f5f5;
      }
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
  code.libinteractive-download {
    background: #eee;
    color: #ccc;
    margin: 1em 0;
    border: 1px dotted #ccc;
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

<script lang="ts">
import { Vue, Component, Emit, Prop, Ref, Watch } from 'vue-property-decorator';
import * as markdown from '../markdown';
import { types } from '../api_types';

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
  @Prop({ default: null }) imageMapping!: markdown.ImageMapping | null;
  @Prop({ default: null })
  problemSettings!: types.ProblemSettingsDistrib | null;
  @Prop({ default: false }) preview!: boolean;

  markdownConverter = new markdown.Converter({ preview: this.preview });

  get html(): string {
    if (this.problemSettings || this.imageMapping) {
      return this.markdownConverter.makeHtmlWithImages(
        this.markdown,
        this.imageMapping || {},
        this.problemSettings || undefined,
      );
    }
    return this.markdownConverter.makeHtml(this.markdown);
  }

  mounted(): void {
    this.renderMathJax();
  }

  @Watch('markdown')
  onMarkdownChanged(val: string, oldVal: string) {
    this.renderMathJax();
  }

  @Emit('rendered')
  private renderMathJax(): void {
    this.root.innerHTML = this.html;
    if (!window.MathJax?.startup) {
      window.MathJax = {
        tex: {
          inlineMath: [
            ['$', '$'],
            ['\\(', '\\)'],
          ],
          processEscapes: true,
          autoload: { color: [], colorV2: ['color'] },
          packages: { '[+]': ['noerrors'] },
        },
        startup: {
          typeset: true,
          elements: [],
          ready: () => {
            if (!window.MathJax?.startup) {
              return;
            }
            if (window.MathJax.startup.defaultReady) {
              window.MathJax.startup.defaultReady();
            }
            if (
              window.MathJax.startup.elements &&
              window.MathJax.startup.elements.length > 0 &&
              window.MathJax.typeset
            ) {
              window.MathJax.typeset(window.MathJax.startup.elements.splice(0));
            }
          },
        },
        options: {
          ignoreHtmlClass: 'tex2jax_ignore',
          processHtmlClass: 'tex2jax_process',
        },
        loader: { load: ['[tex]/noerrors'] },
      };
      // Now that the global MathJax config is set, we can lazily load the
      // library. The element to be rendered will be queued up in
      // MathJax.startup.elements.
      let scriptElement: HTMLScriptElement | null = <HTMLScriptElement | null>(
        document.getElementById('MathJax-script')
      );
      if (!scriptElement) {
        scriptElement = document.createElement('script');
        scriptElement.src = '/third_party/js/mathjax/es5/tex-svg.js';
        scriptElement.id = 'MathJax-script';
        document.head.appendChild(scriptElement);
      }
    }

    if (!window.MathJax.typeset) {
      // In case the library hasn't quite finished loading completely, add the
      // current element to the queue of elements that need to be rendered.
      if (!window.MathJax.startup.elements) {
        window.MathJax.startup.elements = [];
      }
      if (window.MathJax.startup.elements.indexOf(this.root) === -1) {
        window.MathJax.startup.elements.push(this.root);
      }
      return;
    }

    window.MathJax.typeset([this.root]);
  }
}
</script>
