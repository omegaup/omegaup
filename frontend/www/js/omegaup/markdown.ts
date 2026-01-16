import * as Prism from 'prismjs';
import 'prismjs/components/prism-c.js';
import 'prismjs/components/prism-cpp.js';
import 'prismjs/components/prism-csharp.js';
import 'prismjs/components/prism-java.js';
import 'prismjs/components/prism-pascal.js';
import 'prismjs/components/prism-python.js';
import 'prismjs/components/prism-ruby.js';

import * as Markdown from '@/third_party/js/pagedown/Markdown.Converter.js';
import { getSanitizingConverter } from '@/third_party/js/pagedown/Markdown.Sanitizer.js';

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

export class Converter {
  private _converter: Markdown.Converter;
  private _settings?: types.ProblemSettingsDistrib;
  private _sourceMapping?: SourceMapping;
  private _imageMapping?: ImageMapping;

  constructor(options: ConverterOptions = {}) {
    this._converter = getSanitizingConverter();

    // Map of templates.
    const templates: { [key: string]: string } = {};
    if (options.preview) {
      templates['libinteractive:download'] =
        '<code class="libinteractive-download">' +
        '<i class="glyphicon glyphicon-download-alt"></i></code>';
      templates['output-only:download'] =
        '<code class="output-only-download">' +
        '<i class="glyphicon glyphicon-download-alt"></i></code>';
    } else {
      templates[
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
      templates[
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

    const whitelist =
      /^<\/?(a(?:\s+(?:(?:href="(?:(?:mailto:[-A-Za-z0-9+&@#/%?=~_|!:,.;()*[\]$]+)|(?:[a-z/_-]+))")|(?:target="[a-z/_-]+")|(?:class="[a-zA-Z0-9 _-]+")|(?:title="[^"<>]*")))*|details|summary|figure|figcaption|code|i|table|tbody|thead|tr|th(?: align="\w+")?|td(?: align="\w+")?|iframe(?: (?:src="https:\/\/www\.youtube\.com\/embed\/[\w-]+"|(?:width|height|allowfullscreen|frameborder|allow|title)(?:="[^"]+")?))*|iframe(?: (?:src="https:\/\/www\.facebook\.com\/plugins\/video.php\?[\w\d%-_]+"|(?:width|height|scrolling|allowTransparency|allowFullScreen|frameborder)(?:="[^"]+")?))*|div|h3|span|form(?: role="\w+")*|label|select|option(?: (value|selected)="\w+")*|strong|span|button(?: type="\w+")?)(\s+class="[a-zA-Z0-9 _-]+")?>$/i;
    const imageWhitelist = new RegExp(
      '^<img\\ssrc="data:image/[a-zA-Z0-9/;,=+]+"(\\swidth="\\d{1,3}")?(\\sheight="\\d{1,3}")?(\\salt="[^"<>]*")?(\\stitle="[^"<>]*")?\\s?/?>$',
      'i',
    );

    this._converter.hooks.chain(
      'isValidTag',
      (tag: string): boolean =>
        tag.match(whitelist) != null || tag.match(imageWhitelist) != null,
    );

    // These two functions are adapted from Markdown.Converter.js. They are
    // needed to support images with some special characters in their name.
    const escapeCharacters = (
      text: string,
      charsToEscape: string,
      afterBackslash: boolean = false,
      doNotEscapeTildeAndDollar: boolean = false,
    ): string => {
      // First we have to escape the escape characters so that
      // we can build a character class out of them
      let regexString = `([${charsToEscape.replace(/([[\]\\])/g, '\\$1')}])`;

      if (afterBackslash) {
        regexString = `\\\\${regexString}`;
      }

      const regex = new RegExp(regexString, 'g');
      if (!doNotEscapeTildeAndDollar) {
        text = text.replace(/~/g, '~T').replace(/\$/g, '~D');
      }
      return text.replace(regex, (wholeMatch, m1) => `~E${m1.charCodeAt(0)}E`);
    };
    const unescapeCharacters = (text: string): string => {
      //
      // Swap back in all the special characters we've hidden.
      //
      return text
        .replace(/~E(\d+)E/g, (wholeMatch: string, m1: string): string => {
          const charCodeToReplace = parseInt(m1);
          return String.fromCharCode(charCodeToReplace);
        })
        .replace(/~D/g, '$')
        .replace(/~T/g, '~');
    };

    this._converter.hooks.chain('postSpanGamut', (text: string): string => {
      // Templates.
      text = text.replace(
        /^\s*\{\{([a-z0-9_-]+:[a-z0-9_-]+)\}\}\s*$/g,
        (wholematch: string, m1: string): string => {
          if (Object.prototype.hasOwnProperty.call(templates, m1)) {
            return templates[m1];
          }
          return `<span class="alert alert-danger" role="alert">Unrecognized template name: ${m1}</span>`;
        },
      );
      // File transclusion.
      const sourceMapping: ImageMapping = this._sourceMapping || {};
      text = text.replace(
        /^\s*\{\{([a-z0-9_-]+\.[a-z]{1,4})\}\}\s*$/gi,
        (wholematch: string, m1: string): string => {
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

          if (Object.prototype.hasOwnProperty.call(Prism.languages, language)) {
            contents = Prism.highlight(
              contents,
              Prism.languages[language],
              language,
            );
          } else {
            contents = contents
              .replace(/&/g, '&amp;')
              .replace(/</g, '&lt;')
              .replace(/>/g, '&gt;');
          }
          return `<pre><code${className}>${contents}</code></pre>`;
        },
      );
      // Images.
      const imageMapping: ImageMapping = this._imageMapping || {};
      text = text.replace(
        /<img src="([^"]+)"\s*([^>]+)>/g,
        (wholeMatch: string, url: string, attributes: string): string => {
          url = unescapeCharacters(url);
          if (
            url.indexOf('/') != -1 ||
            !Object.prototype.hasOwnProperty.call(imageMapping, url)
          ) {
            return wholeMatch;
          }
          return `<img src="${escapeCharacters(
            imageMapping[url],
            '*_',
          )}" ${attributes}>`;
        },
      );
      // Figures.
      text = text.replace(
        /^\s*<img src="([^"]+)"\s*([^>]+)\s+title="([^"]+)"\s*\/>\s*$/g,
        (wholeMatch: string, url: string, attributes: string, title: string) =>
          `<figure><img src="${url}" ${attributes} />` +
          `<figcaption>${title}</figcaption></figure>`,
      );
      return text;
    });
    this._converter.hooks.chain(
      'postNormalization',
      (text: string, blockGamut: (text: string) => string): string => {
        // Sample I/O table.
        const settings = this._settings;
        return text.replace(
          /^( {0,3}\|\| *(?:input|examplefile) *\n(?:.|\n)+?\n) {0,3}\|\| *end *\n/gm,
          (whole: string, inner: string): string => {
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
            const escapeSample = (
              contents: string,
              doNotEscapeTildeAndDollar: boolean = false,
            ): string =>
              escapeCharacters(
                contents
                  .replace(/\s+$/, '')
                  .replace(/&/g, '&amp;')
                  .replace(/</g, '&lt;')
                  .replace(/>/g, '&gt;'),
                ' \t*_{}[]()<>#+=.!|`-',
                /*afterBackslash=*/ false,
                doNotEscapeTildeAndDollar,
              );
            for (let i = 1; i < matches.length; i += 2) {
              if (matches[i] == 'description') {
                result += '<td>' + blockGamut(matches[i + 1]) + '</td>';
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
                // eslint-disable-next-line no-prototype-builtins
                if (settings?.cases.hasOwnProperty(exampleFilename)) {
                  exampleFile = settings.cases[exampleFilename];
                }
                result += `<td><pre>${escapeSample(
                  exampleFile['in'],
                )}</pre></td>`;
                result += `<td><pre>${escapeSample(
                  exampleFile.out,
                )}</pre></td>`;
                columns += 2;
              } else {
                // Since the match has already gone through escaping, we need
                // to unescape its contents.
                result += `<td><pre>${escapeSample(
                  matches[i + 1],
                  /*doNotEscapeTildeAndDollar=*/ true,
                )}</pre></td>`;
                columns++;
              }
            }
            while (columns < (description_column ? 3 : 2)) {
              result += '<td></td>';
              columns++;
            }
            result += '</tr>\n</tbody>';

            return '<table class="sample_io">\n' + result + '\n</table>\n';
          },
        );
      },
    );
    this._converter.hooks.chain(
      'preBlockGamut',
      (
        text: string,
        blockGamut: (text: string) => string, // eslint-disable-line @typescript-eslint/no-unused-vars
        spanGamut: (text: string) => string, // eslint-disable-line @typescript-eslint/no-unused-vars
      ): string => {
        // GitHub-flavored fenced code blocks
        const fencedCodeBlock = (
          whole: string,
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
            if (
              Object.prototype.hasOwnProperty.call(languageMapping, language)
            ) {
              language = languageMapping[language];
            }
          }

          if (
            language &&
            Object.prototype.hasOwnProperty.call(Prism.languages, language)
          ) {
            contents = Prism.highlight(
              contents,
              Prism.languages[language],
              language,
            );
          } else {
            contents = contents
              .replace(/&/g, '&amp;')
              .replace(/</g, '&lt;')
              .replace(/>/g, '&gt;');
          }

          if (indentation !== '') {
            // Delete any extra indentation spaces from each line.
            const stripPrefix = new RegExp('^ {0,' + indentation.length + '}');
            contents = contents
              .split('\n')
              .map((line) => line.replace(stripPrefix, ''))
              .join('\n');
          }
          contents = escapeCharacters(
            contents,
            ' \t*_{}[]()<>#+=.!|`-',
            /*afterBackslash=*/ false,
            /*doNotEscapeTildeAnDollar=*/ true,
          );
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
            '^( {0,3})((?:~T){3,})(?!~)([^\\n]*)\\n((.|\\n)*?\\n|) {0,3}\\2(?:~T)* *$',
            'gm',
          ),
          fencedCodeBlock,
        );
      },
    );
    this._converter.hooks.chain(
      'preBlockGamut',
      (
        text: string,
        blockGamut: (text: string) => string,
        spanGamut: (text: string) => string,
      ): string => {
        // GitHub-flavored Markdown table.
        return text.replace(
          /^ {0,3}\|[^\n]*\|[ \t]*(\n {0,3}\|[^\n]*\|[ \t]*)+$/gm,
          // eslint-disable-next-line @typescript-eslint/no-unused-vars
          (whole: string, inner: string): string => {
            let cells = whole
              .trim()
              .split('\n')
              .map((line: string) => {
                const m = line.match(/(\\\||[^|])+/g);
                if (!m) return '';
                return m.map((value: string) =>
                  value.trim().replace(/\\\|/g, '|'),
                );
              });

            // The header row must match the delimiter row in the
            // number of cells. If not, a table will not be
            // recognized.
            if (cells.length < 2) {
              return whole;
            }

            const header = cells[0];
            const delimiter = cells[1];
            const alignment = [];
            if (header.length != delimiter.length) {
              return whole;
            }
            cells = cells.slice(2);

            // The delimiter row consists of cells whose only
            // content are hyphens (-), and optionally, a leading or
            // trailing colon (:), or both, to indicate left, right,
            // or center alignment respectively.
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

            let text = '<table>\n';
            text += '<thead>\n';
            text += '<tr>\n';
            for (let i = 0; i < header.length; i++) {
              text +=
                alignedTag('th', alignment[i]) +
                spanGamut(header[i]) +
                '</th>\n';
            }
            text += '</tr>\n';
            text += '</thead>\n';
            if (cells.length) {
              text += '<tbody>\n';
              for (let i = 0; i < cells.length; i++) {
                text += '<tr>\n';
                const row = cells[i];
                for (
                  let j = 0;
                  j < Math.min(alignment.length, row.length);
                  j++
                ) {
                  text +=
                    alignedTag('td', alignment[j]) +
                    spanGamut(row[j]) +
                    '</td>\n';
                }
                for (let j = row.length; j < alignment.length; j++) {
                  text += alignedTag('td', alignment[j]) + '</td>\n';
                }
                text += '</tr>\n';
              }
              text += '</tbody>\n';
            }
            text += '</table>\n';

            return text;
          },
        );
      },
    );

    this._converter.hooks.chain('postConversion', (text: string): string => {
      return text.replace(
        /<a href="([^"]+?)"/g,
        (match: string, url: string) => {
          if (url.startsWith(window.location.origin)) {
            return match;
          }
          return `${match} target="_blank" rel="noopener noreferrer"`;
        },
      );
    });
  }

  public get converter(): Markdown.Converter {
    return this._converter;
  }

  public makeHtml(markdown: string): string {
    return this._converter.makeHtml(markdown);
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
      return this._converter.makeHtml(markdown);
    } finally {
      delete this._imageMapping;
      delete this._settings;
    }
  }
}
