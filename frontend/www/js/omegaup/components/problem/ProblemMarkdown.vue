<template>
  <div
    ref="root"
    data-markdown-statement
    :class="{ 'full-width': fullWidth }"
  ></div>
</template>

<script lang="ts">
import { Vue, Component, Emit, Prop, Ref, Watch } from 'vue-property-decorator';
import * as markdown from '../../markdown';
import * as ui from '../../ui';
import { types } from '../../api_types';
import LibinteractiveDownload from './templates/LibinteractiveDownload.vue';
import OutputOnlyDownload from './templates/OutputOnlyDownload.vue';

import T from '../../lang';

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
export default class ProblemMarkdown extends Vue {
  @Prop() markdown!: string;
  @Ref() root!: HTMLElement;
  @Prop({ default: null }) imageMapping!: markdown.ImageMapping | null;
  @Prop({ default: null }) sourceMapping!: markdown.SourceMapping | null;
  @Prop({ default: null })
  problemSettings!: types.ProblemSettingsDistrib | null;
  @Prop({ default: false }) preview!: boolean;
  @Prop({ default: false }) fullWidth!: boolean;

  private vueInstances: Vue[] = [];

  markdownConverter = new markdown.Converter({ preview: false });

  get html(): string {
    if (this.problemSettings || this.imageMapping) {
      return this.markdownConverter.makeHtmlWithImages(
        this.markdown,
        this.imageMapping || {},
        this.sourceMapping || {},
        this.problemSettings || undefined,
      );
    }
    return this.markdownConverter.makeHtml(this.markdown);
  }

  mounted(): void {
    this.renderMathJax();
    this.injectTemplates();
    // NOTE: When binding markdown input via <textarea>, use v-model.lazy to ensure
    // changes from pagedown controls are reflected correctly
  }

  @Watch('markdown')
  onMarkdownChanged() {
    this.renderMathJax();
  }

  @Emit('rendered')
  private renderMathJax(): void {
    this.root.innerHTML = this.html;
    this.root
      .querySelectorAll(
        '[data-markdown-statement] > pre, .sample_io > tbody > tr > td:first-of-type > pre',
      )
      .forEach((preElement) => {
        if (!preElement.firstChild) {
          return;
        }
        const inputValue = (preElement as HTMLElement).innerText;

        const clipboardButton = document.createElement('button');
        clipboardButton.appendChild(document.createTextNode('📋'));
        clipboardButton.title = T.wordsCopyToClipboard;
        clipboardButton.className = 'clipboard btn btn-light';

        clipboardButton.addEventListener('click', (event: Event) => {
          event.preventDefault();
          event.stopPropagation();
          ui.copyToClipboard(inputValue);
        });

        (preElement.parentElement as HTMLElement).insertBefore(
          clipboardButton,
          preElement,
        );
      });
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
      let scriptElement = document.getElementById(
        'MathJax-script',
      ) as HTMLScriptElement | null;
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

  private injectTemplates(): void {
    const templateComponents: Record<string, any> = {
      'libinteractive:download': LibinteractiveDownload,
      'output-only:download': OutputOnlyDownload,
    };

    const templates = this.root.querySelectorAll(
      'template[data-template-name]',
    );
    for (const template of templates) {
      const name = template.getAttribute('data-template-name');
      if (!name || !templateComponents[name]) continue;

      const mountPoint = document.createElement('div');
      template.replaceWith(mountPoint);

      const app = new Vue({
        render: (h) => h(templateComponents[name]),
      });
      app.$mount(mountPoint);

      this.vueInstances.push(app);
    }
  }

  beforeDestroy() {
    for (const app of this.vueInstances) {
      app.$destroy();
      if (app.$el && app.$el.parentNode) {
        app.$el.parentNode.removeChild(app.$el);
      }
    }
    this.vueInstances = [];
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
@import '../../../../../../node_modules/prismjs/themes/prism.css';
@import '../../../../sass/main.scss';

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
