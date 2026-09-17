import * as Prism from 'prismjs';
import 'prismjs/components/prism-c.js';
import 'prismjs/components/prism-cpp.js';
import 'prismjs/components/prism-csharp.js';
import 'prismjs/components/prism-java.js';
import 'prismjs/components/prism-pascal.js';
import 'prismjs/components/prism-python.js';
import 'prismjs/components/prism-ruby.js';

import MarkdownIt from 'markdown-it';

import T from './lang';
import { types } from './api_types';

export type SourceMapping = {
  [filename: string]: string;
};

export type ImageMapping = {
  [url: string]: string;
};

export interface ConverterOptions {
  preview?: boolean;
}

const languageMapping: { [key: string]: string } = {
  c: 'c',
  'c11-clang': 'c',
  'c11-gcc': 'c',
  cpp: 'cpp',
  'cpp11-clang': 'cpp',
  'cpp11-gcc': 'cpp',
  'cpp17-clang': 'cpp',
  'cpp17-gcc': 'cpp',
  'cpp20-clang': 'cpp',
  'cpp20-gcc': 'cpp',
  java: 'java',
  kt: 'kotlin',
  py: 'python',
  py2: 'python',
  py3: 'python',
  rb: 'ruby',
  cs: 'csharp',
  pas: 'pascal',
  hs: 'haskell',
  lua: 'lua',
  go: 'go',
  rs: 'rust',
  js: 'javascript',
};

const extraTagWhitelist = /^<\/?(a(?:\s+(?:(?:href="(?:(?:mailto:[-A-Za-z0-9+&@#/%?=~_|!:,.;()*[\]$]+)|(?:[a-z/_-]+))")|(?:target="[a-z/_-]+")|(?:class="[a-zA-Z0-9 _-]+")|(?:title="[^"<>]*")))*|details|summary|figure|figcaption|code|i|table|tbody|thead|tr|th(?: align="\w+")?|td(?: align="\w+")?|iframe(?: (?:src="https:\/\/www\.youtube\.com\/embed\/[\w-]+"|(?:width|height|allowfullscreen|frameborder|allow|title)(?:="[^"]+")?))*|iframe(?: (?:src="https:\/\/www\.facebook\.com\/plugins\/video.php\?[\w\d%-_]+"|(?:width|height|scrolling|allowTransparency|allowFullScreen|frameborder)(?:="[^"]+")?))*|div|h3|span|form(?: role="\w+")*|label|select|option(?: (value|selected)="\w+")*|strong|span|button(?: type="\w+")?)(\s+class="[a-zA-Z0-9 _-]+")?>$/i;

const imageWhitelist = new RegExp(
  '^<img\\ssrc="data:image/[a-zA-Z0-9/;,=+]+"(\\swidth="\\d{1,3}")?(\\sheight="\\d{1,3}")?(\\salt="[^"<>]*")?(\\stitle="[^"<>]*")?\\s?/?>$',
  'i',
);

const basicTagWhitelist = /^(<\/?(b|blockquote|code|del|dd|dl|dt|em|h1|h2|h3|i|kbd|li|ol(?: start="\d+")?|p|pre|s|sup|sub|strong|strike|ul)>|<(br|hr)\s?\/?>)$/i;
const anchorWhitelist = /^(<a\shref="((https?|ftp):\/\/|\/)[-A-Za-z0-9+&@#/%?=~_|!:,.;()*[\]$]+"(\stitle="[^"<>]+")?\s?>|<\/a>)$/i;
const imgSrcWhitelist = /^(<img\ssrc="(https?:\/\/|\/)[-A-Za-z0-9+&@#/%?=~_|!:,.;()*[\]$]+"(\swidth="\d{1,3}")?(\sheight="\d{1,3}")?(\salt="[^"<>]*")?(\stitle="[^"<>]*")?\s?\/?>)$/i;

function isValidTag(tag: string): boolean {
  return (
    basicTagWhitelist.test(tag) ||
    anchorWhitelist.test(tag) ||
    imgSrcWhitelist.test(tag) ||
    extraTagWhitelist.test(tag) ||
    imageWhitelist.test(tag)
  );
}

function sanitizeTag(tag: string): string {
  if (isValidTag(tag)) {
    return tag;
  }
  let anyChange = false;
  const encoded = tag.replace(
    /^(<a href="|<img src=")([^"]*)/i,
    (_wholematch, prefix: string, url: string) => {
      return (
        prefix +
        url.replace(/[^-A-Za-z0-9+&@#/%?=~_|!:,.;()*[\]$]/g, (c: string) => {
          anyChange = true;
          if (c == "'") {
            return '%27';
          }
          return encodeURIComponent(c);
        })
      );
    },
  );
  if (
    anyChange &&
    (anchorWhitelist.test(encoded) || imgSrcWhitelist.test(encoded))
  ) {
    return encoded;
  }
  return '';
}

function sanitizeHtml(html: string): string {
  return html.replace(/<[^>]*>?/gi, (tag) => sanitizeTag(tag));
}

function balanceTags(html: string): string {
  if (html == '') {
    return '';
  }
  const re = /<\/?\w+[^>]*(\s|$|>)/g;
  const tags = html.toLowerCase().match(re);
  const tagcount = (tags || []).length;
  if (tagcount == 0) {
    return html;
  }
  const ignoredtags = '<p><img><br><li><hr>';
  const tagpaired: boolean[] = [];
  const tagremove: boolean[] = [];
  let needsRemoval = false;

  for (let ctag = 0; ctag < tagcount; ctag++) {
    const tagname = (tags as RegExpMatchArray)[ctag].replace(
      /<\/?(\w+).*/,
      '$1',
    );
    if (tagpaired[ctag] || ignoredtags.search('<' + tagname + '>') > -1) {
      continue;
    }
    const tag = (tags as RegExpMatchArray)[ctag];
    let match = -1;
    if (!/^<\//.test(tag)) {
      for (let ntag = ctag + 1; ntag < tagcount; ntag++) {
        if (
          !tagpaired[ntag] &&
          (tags as RegExpMatchArray)[ntag] == '</' + tagname + '>'
        ) {
          match = ntag;
          break;
        }
      }
    }
    if (match == -1) {
      needsRemoval = tagremove[ctag] = true;
    } else {
      tagpaired[match] = true;
    }
  }
  if (!needsRemoval) {
    return html;
  }
  let ctag = 0;
  return html.replace(re, (match) => {
    const res = tagremove[ctag] ? '' : match;
    ctag++;
    return res;
  });
}

function unescapeCharacters(text: string): string {
  return text
    .replace(/~E(\d+)E/g, (_wholeMatch: string, m1: string): string => {
      const charCodeToReplace = parseInt(m1, 10);
      return String.fromCharCode(charCodeToReplace);
    })
    .replace(/~D/g, '$')
    .replace(/~T/g, '~');
}

function highlightCode(contents: string, language: string | null): string {
  if (
    language &&
    Object.prototype.hasOwnProperty.call(languageMapping, language)
  ) {
    language = languageMapping[language];
  }
  if (
    language &&
    Object.prototype.hasOwnProperty.call(Prism.languages, language)
  ) {
    return Prism.highlight(contents, Prism.languages[language], language);
  }
  return contents
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;');
}

export class Converter {
  private md: MarkdownIt;
  private _settings?: types.ProblemSettingsDistrib;
  private _sourceMapping?: SourceMapping;
  private _imageMapping?: ImageMapping;
  private _mermaidLoaded = false;
  private templates: { [key: string]: string };
  private htmlBlocks: string[] = [];

  constructor(options: ConverterOptions = {}) {
    this.md = new MarkdownIt({
      html: true,
      xhtmlOut: false,
      breaks: false,
      linkify: false,
      typographer: false,
    });
    this.md.disable(['fence']);

    this.templates = {};
    if (options.preview) {
      this.templates['libinteractive:download'] =
        '<code class="libinteractive-download">📥</code>';
      this.templates['output-only:download'] =
        '<code class="output-only-download">📥</code>';
    } else {
      this.templates[
        'libinteractive:download'
      ] = `<div class="libinteractive-download panel panel-default">
        <div class="panel-heading">
          <h3 class="panel-title">
            ${T.libinteractiveTitle}
            <a class="libinteractive-help" target="_blank" href="/libinteractive/${T.locale}/contest/"><span class="glyphicon glyphicon-question-sign"></span></a>
          </h3>
        </div>
        <div class="panel-body">
          <form role="form">
            <div class="form-horizontal">
              <div class="form-group row">
                <label class="col-xs-6 col-sm-2 control-label">${T.libinteractiveOs}</label>
                <div class="col-xs-6 col-sm-10">
                  <select class="form-control download-os">
                    <option value="unix">Linux/Mac OS X</option>
                    <option value="windows">Windows</option>
                  </select>
                </div>
              </div>
              <div class="form-group row">
                <label class="col-xs-6 col-sm-2 control-label">${T.libinteractiveLanguage}</label>
                <div class="col-xs-6 col-sm-10">
                  <select class="form-control download-lang">
                    <option value="c" selected="selected">C</option>
                    <option value="cpp">C++</option>
                    <option value="java">Java</option>
                    <option value="py">Python</option>
                    <option value="pas">Pascal</option>
                  </select>
                </div>
              </div>
              <div class="form-group row">
                <strong class="col-xs-6 col-sm-2 control-label">${T.libinteractiveFilename}</strong>
                <div class="col-xs-6 col-sm-10">
                  <span class="libinteractive-interface-name"></span>.<span class="libinteractive-extension">c</span>
                </div>
              </div>
              <div class="form-group row">
                <div class="col-xs-12 col-sm-offset-2 offset-sm-2 col-sm-10">
                  <button type="submit" class="btn btn-primary active">
                    ${T.libinteractiveDownload}
                  </button>
                </div>
              </div>
            </div>
          </form>
        </div>
      </div>`;
      this.templates[
        'output-only:download'
      ] = `<div class="output-only-download panel panel-default">
        <div class="panel-heading">
          <h3 class="panel-title">
            ${T.outputOnlyDownloadInput}
          </h3>
        </div>
        <div class="panel-body">
          <form role="form">
            <div class="form-horizontal">
              <div class="form-group row">
                <div class="col-12 text-center">
                  <a class="btn btn-primary">
                    ${T.outputOnlyDownloadInput}
                  </a>
                </div>
              </div>
            </div>
          </form>
        </div>
      </div>`;
    }
  }

  public makeHtml(markdown: string): string {
    return this.convert(markdown);
  }

  public makeHtmlWithImages(
    markdown: string,
    imageMapping: ImageMapping,
    sourceMapping: SourceMapping,
    settings?: types.ProblemSettingsDistrib,
  ): string {
    try {
      this._imageMapping = imageMapping;
      this._sourceMapping = sourceMapping;
      this._settings = settings;
      return this.convert(markdown);
    } finally {
      delete this._imageMapping;
      delete this._sourceMapping;
      delete this._settings;
    }
  }

  public async renderMermaidDiagrams(container: HTMLElement): Promise<void> {
    const codeBlocks = container.querySelectorAll(
      'pre > code.language-mermaid',
    );

    if (codeBlocks.length === 0) {
      return;
    }

    try {
      const mermaidModule = await import('mermaid');
      const mermaid = mermaidModule.default;

      if (!this._mermaidLoaded) {
        mermaid.initialize({
          startOnLoad: false,
          theme: 'default',
          securityLevel: 'strict',
          fontFamily: 'trebuchet ms, verdana, arial, sans-serif',
          flowchart: {
            htmlLabels: false,
          },
        });
        this._mermaidLoaded = true;
      }

      const renderPromises = Array.from(codeBlocks).map(
        async (block, index) => {
          let code = block.textContent || '';
          const id = `mermaid-diagram-${Date.now()}-${index}`;
          const pre = block.parentElement;

          if (!pre) return;
          code = this.preprocessFontAwesomeIcons(code);

          const tempContainer = document.createElement('div');
          tempContainer.style.position = 'absolute';
          tempContainer.style.left = '-9999px';
          tempContainer.style.visibility = 'hidden';
          tempContainer.innerHTML = `<div class="mermaid">${code}</div>`;
          document.body.appendChild(tempContainer);

          try {
            const { svg } = await mermaid.render(id, code);
            document.body.removeChild(tempContainer);

            const wrapper = document.createElement('div');
            wrapper.className = 'mermaid-diagram';
            wrapper.innerHTML = svg;

            pre.replaceWith(wrapper);
          } catch (error: any) {
            console.error('Mermaid rendering error:', error);
            if (document.body.contains(tempContainer)) {
              document.body.removeChild(tempContainer);
            }
            pre.classList.add('mermaid-error');

            const errorMsg = document.createElement('div');
            errorMsg.className = 'mermaid-error-message';
            errorMsg.textContent = `Error rendering diagram: ${error.message}`;

            pre.parentElement?.insertBefore(errorMsg, pre);

            throw error;
          }
        },
      );

      await Promise.all(renderPromises);
    } catch (error) {
      console.error('Error loading/rendering mermaid:', error);
      throw error;
    }
  }

  private preprocessFontAwesomeIcons(code: string): string {
    const iconMap: { [key: string]: string } = {
      desktop: '💻',
      terminal: '⌨️',
      cloud: '☁️',
      database: '🗄️',
      server: '🖥️',
      laptop: '💻',
      mobile: '📱',
      code: '💾',
      users: '👥',
      user: '👤',
      cog: '⚙️',
      cogs: '⚙️',
      check: '✓',
      times: '✗',
      'arrow-right': '→',
      'arrow-left': '←',
      play: '▶',
      stop: '■',
    };

    return code.replace(/fa:fa-([\w-]+)/g, (_match, iconName) => {
      return iconMap[iconName] || '';
    });
  }

  private convert(markdown: string): string {
    this.htmlBlocks = [];
    // Pagedown normalized CRLF, stripped space-only lines, and guaranteed a
    // trailing blank line so block-level dialect hooks could match at EOF.
    let text = markdown.replace(/\r\n/g, '\n').replace(/\r/g, '\n');
    text = text.replace(/^[ \t]+$/gm, '');
    if (!text.endsWith('\n')) {
      text += '\n';
    }
    text += '\n';
    text = this.replaceSampleIo(text);
    text = this.replaceFencedCode(text);
    text = this.replaceGfmTables(text);
    let html = this.md.render(text);
    html = this.restoreHtmlBlocks(html);
    html = this.replaceTemplatesAndSources(html);
    html = this.remapImages(html);
    html = this.wrapFigures(html);
    html = sanitizeHtml(html);
    html = balanceTags(html);
    html = this.annotateExternalLinks(html);
    return html.replace(/\n+$/, '');
  }

  private protectHtml(html: string): string {
    const id = this.htmlBlocks.length;
    this.htmlBlocks.push(html);
    return `\n\n<!--ΩUPBLK${id}-->\n\n`;
  }

  private restoreHtmlBlocks(html: string): string {
    return html.replace(/<!--ΩUPBLK(\d+)-->/g, (_match, id: string) => {
      return this.htmlBlocks[Number(id)] ?? '';
    });
  }

  private replaceFencedCode(text: string): string {
    const fencedCodeBlock = (
      _whole: string,
      indentation: string,
      fence: string,
      infoString: string,
      contents: string,
    ) => {
      let className = '';
      let language: string | null = null;
      infoString = infoString.trim();
      if (infoString != '') {
        language = infoString.split(/\s+/)[0];
        className = ` class="language-${language}"`;
      }
      contents = highlightCode(contents, language);
      if (indentation !== '') {
        const stripPrefix = new RegExp('^ {0,' + indentation.length + '}');
        contents = contents
          .split('\n')
          .map((line) => line.replace(stripPrefix, ''))
          .join('\n');
      }
      return `<pre><code${className}>${contents}</code></pre>`;
    };
    text = text.replace(
      new RegExp(
        '^( {0,3})(`{3,})([^`\\n]*)\\n((?:.|\\n)*?\\n|) {0,3}\\2`* *$',
        'gm',
      ),
      fencedCodeBlock,
    );
    return text.replace(
      new RegExp(
        '^( {0,3})(~{3,})(?!~)([^\\n]*)\\n((.|\\n)*?\\n|) {0,3}\\2~* *$',
        'gm',
      ),
      fencedCodeBlock,
    );
  }

  private replaceGfmTables(text: string): string {
    return text.replace(
      /^ {0,3}\|[^\n]*\|[ \t]*(\n {0,3}\|[^\n]*\|[ \t]*)+$/gm,
      (whole: string): string => {
        let cells = whole
          .trim()
          .split('\n')
          .map((line: string) => {
            const m = line.match(/(\\\||[^|])+/g);
            if (!m) return '';
            return m.map((value: string) => value.trim().replace(/\\\|/g, '|'));
          });

        if (cells.length < 2) {
          return whole;
        }

        const header = cells[0];
        const delimiter = cells[1];
        const alignment: string[] = [];
        if (header.length != delimiter.length) {
          return whole;
        }
        cells = cells.slice(2);

        for (let i = 0; i < delimiter.length; i++) {
          if (!delimiter[i].match(/^:?-+:?$/)) {
            return whole;
          }
          if (
            delimiter[i][0] == ':' &&
            delimiter[i][delimiter[i].length - 1] == ':'
          ) {
            alignment.push('center');
          } else if (delimiter[i][delimiter[i].length - 1] == ':') {
            alignment.push('right');
          } else {
            alignment.push('');
          }
        }

        const alignedTag = (tagName: string, align: string) =>
          '<' + tagName + (align ? ` align="${align}"` : '') + '>';

        let html = '<table>\n';
        html += '<thead>\n';
        html += '<tr>\n';
        for (let i = 0; i < header.length; i++) {
          html +=
            alignedTag('th', alignment[i]) +
            this.md.renderInline(header[i]) +
            '</th>\n';
        }
        html += '</tr>\n';
        html += '</thead>\n';
        if (cells.length) {
          html += '<tbody>\n';
          for (let i = 0; i < cells.length; i++) {
            html += '<tr>\n';
            const row = cells[i];
            for (let j = 0; j < Math.min(alignment.length, row.length); j++) {
              html +=
                alignedTag('td', alignment[j]) +
                this.md.renderInline(row[j]) +
                '</td>\n';
            }
            for (let j = row.length; j < alignment.length; j++) {
              html += alignedTag('td', alignment[j]) + '</td>\n';
            }
            html += '</tr>\n';
          }
          html += '</tbody>\n';
        }
        html += '</table>\n';
        return html;
      },
    );
  }

  private replaceSampleIo(text: string): string {
    const settings = this._settings;
    return text.replace(
      /^( {0,3}\|\| *(?:input|examplefile) *\n(?:.|\n)+?\n) {0,3}\|\| *end *\n/gm,
      (_whole: string, inner: string): string => {
        const matches = inner.split(
          / {0,3}\|\| *(examplefile|input|output|description) *\n/,
        );
        let result = '';
        let description_column = false;
        for (let i = 1; i < matches.length; i += 2) {
          if (matches[i] == 'description') {
            description_column = true;
            break;
          }
        }
        result += '<thead><tr>';
        result += `<th>${T.wordsInput}</th>`;
        result += `<th>${T.wordsOutput}</th>`;
        if (description_column) {
          result += `<th>${T.wordsDescription}</th>`;
        }
        result += '</tr></thead>\n';
        let first_row = true;
        let columns = 0;
        result += '<tbody>';
        const escapeSample = (contents: string): string =>
          contents
            .replace(/\s+$/, '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;');
        for (let i = 1; i < matches.length; i += 2) {
          if (matches[i] == 'description') {
            result += '<td>' + this.md.render(matches[i + 1]).trim() + '</td>';
            columns++;
            continue;
          }

          if (matches[i] == 'input' || matches[i] == 'examplefile') {
            if (!first_row) {
              while (columns < (description_column ? 3 : 2)) {
                result += '<td></td>';
                columns++;
              }
              result += '</tr>\n';
            }
            first_row = false;
            result += '<tr>';
            columns = 0;
          }

          if (matches[i] == 'examplefile') {
            const exampleFilename = matches[i + 1].trim();
            let exampleFile = {
              in: `{{examples/${exampleFilename}.in}}`,
              out: `{{examples/${exampleFilename}.out}}`,
            };
            if (
              settings?.cases &&
              Object.prototype.hasOwnProperty.call(
                settings.cases,
                exampleFilename,
              )
            ) {
              exampleFile = settings.cases[exampleFilename];
            }
            result += `<td><pre>${escapeSample(exampleFile['in'])}</pre></td>`;
            result += `<td><pre>${escapeSample(exampleFile.out)}</pre></td>`;
            columns += 2;
          } else {
            result += `<td><pre>${escapeSample(matches[i + 1])}</pre></td>`;
            columns++;
          }
        }
        while (columns < (description_column ? 3 : 2)) {
          result += '<td></td>';
          columns++;
        }
        result += '</tr>\n</tbody>';

        return this.protectHtml(
          '<table class="sample_io">\n' + result + '\n</table>\n',
        );
      },
    );
  }

  private replaceTemplatesAndSources(html: string): string {
    const templates = this.templates;
    html = html.replace(
      /\{\{([a-z0-9_-]+:[a-z0-9_-]+)\}\}/g,
      (_wholematch: string, m1: string): string => {
        if (Object.prototype.hasOwnProperty.call(templates, m1)) {
          return templates[m1];
        }
        return `<span class="alert alert-danger" role="alert">Unrecognized template name: ${m1}</span>`;
      },
    );
    const sourceMapping: ImageMapping = this._sourceMapping || {};
    html = html.replace(
      /\{\{([a-z0-9_-]+\.[a-z]{1,4})\}\}/gi,
      (_wholematch: string, m1: string): string => {
        if (!Object.prototype.hasOwnProperty.call(sourceMapping, m1)) {
          return `<span class="alert alert-danger" role="alert">Unrecognized source filename: ${m1}</span>`;
        }
        const extension = m1.split('.')[1];
        let language = extension;
        if (Object.prototype.hasOwnProperty.call(languageMapping, language)) {
          language = languageMapping[language];
        }
        const className = ` class="language-${language}"`;
        let contents = sourceMapping[m1];
        contents = highlightCode(contents, language);
        return `<pre><code${className}>${contents}</code></pre>`;
      },
    );
    return html;
  }

  private remapImages(html: string): string {
    const imageMapping: ImageMapping = this._imageMapping || {};
    return html.replace(
      /<img src="([^"]+)"\s*([^>]*)>/g,
      (wholeMatch: string, url: string, attributes: string): string => {
        url = unescapeCharacters(url);
        if (
          url.indexOf('/') != -1 ||
          !Object.prototype.hasOwnProperty.call(imageMapping, url)
        ) {
          return wholeMatch;
        }
        return `<img src="${imageMapping[url]}" ${attributes}>`;
      },
    );
  }

  private wrapFigures(html: string): string {
    return html.replace(
      /<img src="([^"]+)"\s*([^>]*?)\s+title="([^"]+)"\s*\/?>/g,
      (_wholeMatch: string, url: string, attributes: string, title: string) =>
        `<figure><img src="${url}" ${attributes} />` +
        `<figcaption>${title}</figcaption></figure>`,
    );
  }

  private annotateExternalLinks(html: string): string {
    return html.replace(/<a href="([^"]+?)"/g, (match: string, url: string) => {
      if (url.startsWith(window.location.origin)) {
        return match;
      }
      return `${match} target="_blank" rel="noopener noreferrer"`;
    });
  }
}
