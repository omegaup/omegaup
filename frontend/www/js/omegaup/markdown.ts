import T from './lang';

export type ProblemSettings = any;

declare namespace Markdown {
  export type ImageMapping = {
    [url: string]: string;
  };

  export interface Hooks {
    chain: (eventName: string, callback: any) => void;
  }

  export interface Converter {
    hooks: Markdown.Hooks;
    _settings?: ProblemSettings;
    _imageMapping?: Markdown.ImageMapping;

    makeHtml: (markdown: string) => string;
    makeHtmlWithImages: (
      markdown: string,
      imageMapping: Markdown.ImageMapping,
      settings: ProblemSettings,
    ) => string;
  }

  function getSanitizingConverter(): Markdown.Converter;
}

export function markdownConverter(
  options: {
    preview?: boolean;
    settings?: ProblemSettings;
    imageMapping?: Markdown.ImageMapping;
  } = {},
) {
  // Map of templates.
  const templates: { [key: string]: string } = {};
  if (options.preview) {
    templates['libinteractive:download'] =
      '<code class="libinteractive-download">' +
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
            <div class="form-group">
              <div class="col-sm-10">
                <label class="col-sm-2 control-label">${T.libinteractiveOs}</label>
                <select class="form-control download-os">
                  <option value="unix">Linux/Mac OS X</option>
                  <option value="windows">Windows</option>
                </select>
              </div>
            </div>
            <div class="form-group">
              <div class="col-sm-10">
                <label class="col-sm-2 control-label">${T.libinteractiveLanguage}</label>
                <select class="form-control download-lang">
                  <option value="c" selected="selected">C</option>
                  <option value="cpp">C++</option>
                  <option value="java">Java</option>
                  <option value="py">Python</option>
                  <option value="pas">Pascal</option>
                </select>
              </div>
            </div>
            <div class="form-group">
              <strong class="col-sm-2 control-label">${T.libinteractiveFilename}</strong>
              <div class="col-sm-10">
                <span class="libinteractive-interface-name"></span>.<span class="libinteractive-extension">c</span>
              </div>
            </div>
            <div class="form-group">
              <div class="col-sm-offset-2 col-sm-10">
                <button type="submit" class="btn btn-primary active">
                  ${T.libinteractiveDownload}
                </button>
              </div>
            </div>
          </div>
        </form>
      </div>
    </div>`;
  }

  const converter = Markdown.getSanitizingConverter();
  const whitelist = /^<\/?(a(?: (target|class|href)="[a-z/_-]+")*|figure|figcaption|code|i|table|tbody|thead|tr|th(?: align="\w+")?|td(?: align="\w+")?|div|h3|span|form(?: role="\w+")*|label|select|option(?: (value|selected)="\w+")*|strong|span|button(?: type="\w+")?)( class="[a-zA-Z0-9 _-]+")?>$/i;
  const imageWhitelist = new RegExp(
    '^<img\\ssrc="data:image/[a-zA-Z0-9/;,=+]+"(\\swidth="\\d{1,3}")?(\\sheight="\\d{1,3}")?(\\salt="[^"<>]*")?(\\stitle="[^"<>]*")?\\s?/?>$',
    'i',
  );

  converter.hooks.chain(
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
    let regexString = `([${charsToEscape.replace(/([\[\]\\])/g, '\\$1')}])`;

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

  converter.hooks.chain('postSpanGamut', (text: string): string => {
    // Templates.
    text = text.replace(
      /^\s*\{\{([a-z0-9_:]+)\}\}\s*$/g,
      (wholematch: string, m1: string): string => {
        if (templates.hasOwnProperty(m1)) {
          return templates[m1];
        }
        return (
          '<strong style="color: red">Unrecognized template name: ' +
          m1 +
          '</strong>'
        );
      },
    );
    // Images.
    const imageMapping: Markdown.ImageMapping =
      converter._imageMapping || options.imageMapping || {};
    text = text.replace(
      /<img src="([^"]+)"\s*([^>]+)>/g,
      (wholeMatch: string, url: string, attributes: string): string => {
        url = unescapeCharacters(url);
        if (url.indexOf('/') != -1 || !imageMapping.hasOwnProperty(url)) {
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
  converter.hooks.chain(
    'postNormalization',
    (text: string, blockGamut: (text: string) => string): string => {
      // Sample I/O table.
      let settings = converter._settings || options.settings || { cases: {} };
      return text.replace(
        /^( {0,3}\|\| *(?:input|examplefile) *\n(?:.|\n)+?\n) {0,3}\|\| *end *\n/gm,
        (whole: string, inner: string): string => {
          var matches = inner.split(
            / {0,3}\|\| *(examplefile|input|output|description) *\n/,
          );
          var result = '';
          var description_column = false;
          for (var i = 1; i < matches.length; i += 2) {
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
          result += '</tr></thead>';
          var first_row = true;
          var columns = 0;
          result += '<tbody>';
          for (var i = 1; i < matches.length; i += 2) {
            if (matches[i] == 'description') {
              result += '<td>' + blockGamut(matches[i + 1]) + '</td>';
              columns++;
            } else {
              if (matches[i] == 'input' || matches[i] == 'examplefile') {
                if (!first_row) {
                  while (columns < (description_column ? 3 : 2)) {
                    result += '<td></td>';
                    columns++;
                  }
                  result += '</tr>';
                }
                first_row = false;
                result += '<tr>';
                columns = 0;
              }

              if (matches[i] == 'examplefile') {
                let exampleFilename = matches[i + 1].trim();
                let exampleFile = {
                  in: `{{examples/${exampleFilename}.in}}`,
                  out: `{{examples/${exampleFilename}.out}}`,
                };
                if (settings.cases.hasOwnProperty(exampleFilename)) {
                  exampleFile = settings.cases[exampleFilename];
                }
                result += `<td><pre>${exampleFile['in'].replace(
                  /\s+$/,
                  '',
                )}</pre></td>`;
                result += `<td><pre>${exampleFile.out.replace(
                  /\s+$/,
                  '',
                )}</pre></td>`;
                columns += 2;
              } else {
                result += `<td><pre>${escapeCharacters(
                  matches[i + 1]
                    .replace(/\s+$/, '')
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;'),
                  ' \t*_{}[]()<>#+=.!|`-',
                  /*afterBackslash=*/ false,
                  /*doNotEscapeTildeAnDollar=*/ true,
                )}</pre></td>`;
                columns++;
              }
            }
          }
          while (columns < (description_column ? 3 : 2)) {
            result += '<td></td>';
            columns++;
          }
          result += '</tr></tbody>';

          return '<table class="sample_io">\n' + result + '\n</table>\n';
        },
      );
    },
  );
  converter.hooks.chain(
    'preBlockGamut',
    (
      text: string,
      blockGamut: (text: string) => string,
      spanGamut: (text: string) => string,
    ): string => {
      // GitHub-flavored fenced code blocks
      const fencedCodeBlock = (
        whole: string,
        indentation: string,
        fence: string,
        infoString: string,
        contents: string,
      ) => {
        contents = contents
          .replace(/&/g, '&amp;')
          .replace(/</g, '&lt;')
          .replace(/>/g, '&gt;');
        if (indentation != '') {
          let lines = [];
          let stripPrefix = new RegExp('^ {0,' + indentation.length + '}');
          for (let line of contents.split('\n')) {
            lines.push(line.replace(stripPrefix, ''));
          }
          contents = escapeCharacters(
            lines.join('\n'),
            ' \t*_{}[]()<>#+=.!|`-',
            /*afterBackslash=*/ false,
            /*doNotEscapeTildeAnDollar=*/ true,
          );
        }
        let className = '';
        infoString = infoString.trim();
        if (infoString != '') {
          className = ` class="language-${infoString.split(/\s+/)[0]}"`;
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
          '^( {0,3})((?:~T){3,})(?!~)([^\\n]*)\\n((.|\\n)*?\\n|) {0,3}\\2(?:~T)* *$',
          'gm',
        ),
        fencedCodeBlock,
      );
    },
  );
  converter.hooks.chain(
    'preBlockGamut',
    (
      text: string,
      blockGamut: (text: string) => string,
      spanGamut: (text: string) => string,
    ): string => {
      // GitHub-flavored Markdown table.
      return text.replace(
        /^ {0,3}\|[^\n]*\|[ \t]*(\n {0,3}\|[^\n]*\|[ \t]*)+$/gm,
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

          // The header row must match the delimiter row in the number of
          // cells. If not, a table will not be recognized.
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

          // The delimiter row consists of cells whose only content are
          // hyphens (-), and optionally, a leading or trailing colon (:),
          // or
          // both, to indicate left, right, or center alignment
          // respectively.
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
              alignedTag('th', alignment[i]) + spanGamut(header[i]) + '</th>\n';
          }
          text += '</tr>\n';
          text += '</thead>\n';
          if (cells.length) {
            text += '<tbody>\n';
            for (let i = 0; i < cells.length; i++) {
              text += '<tr>\n';
              const row = cells[i];
              for (let j = 0; j < Math.min(alignment.length, row.length); j++) {
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

  converter.makeHtmlWithImages = (
    markdown: string,
    imageMapping: Markdown.ImageMapping,
    settings: ProblemSettings,
  ): string => {
    try {
      converter._imageMapping = imageMapping;
      converter._settings = settings;
      return converter.makeHtml(markdown);
    } finally {
      delete converter._imageMapping;
      delete converter._settings;
    }
  };

  return converter;
}
