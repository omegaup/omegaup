import API from './api.js';
import {T} from './omegaup.js';

let UI = {
  navigateTo: function(url) { window.location = url; },

  escape: function(s) {
    if (typeof s !== 'string') return '';
    return s.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
  },

  formatDelta: function(delta) {
    let days = Math.floor(delta / (24 * 60 * 60 * 1000));
    delta -= days * (24 * 60 * 60 * 1000);
    let hours = Math.floor(delta / (60 * 60 * 1000));
    delta -= hours * (60 * 60 * 1000);
    let minutes = Math.floor(delta / (60 * 1000));
    delta -= minutes * (60 * 1000);
    let seconds = Math.floor(delta / 1000);

    let clock = '';

    if (days > 0) {
      clock += days + ':';
    }
    if (hours < 10) clock += '0';
    clock += hours + ':';
    if (minutes < 10) clock += '0';
    clock += minutes + ':';
    if (seconds < 10) clock += '0';
    clock += seconds;

    return clock;
  },

  isVirtual: function(contest) { return contest.rerun_id != 0; },

  contestTitle: function(contest) {
    if (UI.isVirtual(contest)) {
      return UI.formatString(T.virtualContestSuffix, {title: contest.title});
    }
    return contest.title;
  },

  rankingUsername: function(rank) {
    let username = rank.username;
    if (rank.name != rank.username) username += ` (${UI.escape(rank.name)})`;
    if (rank.virtual)
      username = UI.formatString(T.virtualSuffix, {username: username});
    return username;
  },

  formatString: function(template, values) {
    for (var key in values) {
      if (!values.hasOwnProperty(key)) continue;
      template =
          template.replace(new RegExp('%\\(' + key + '\\)', 'g'), values[key]);
    }
    return template;
  },

  contestUpdated: function(data, contestAlias) {
    if (data.status != 'ok') {
      UI.error(data.error || 'error');
      return;
    }
    UI.success(omegaup.T.contestEditContestEdited + ' <a href="/arena/' +
               contestAlias + '">' + T.contestEditGoToContest + '</a>');
  },

  displayStatus: function(message, type) {
    if ($('#status .message').length == 0) {
      console.error('Showing warning but there is no status div');
    }

    // Just in case this needs to be displayed but the UI wasn't set up yet.
    $('#loading').hide();
    $('#root').show();

    $('#status .message').html(message);
    $('#status')
        .removeClass('alert-success alert-info alert-warning alert-danger')
        .addClass(type)
        .slideDown();
    if (type == 'alert-success') {
      setTimeout(UI.dismissNotifications, 5000);
    }
  },

  error: function(message) { UI.displayStatus(message, 'alert-danger'); },

  info: function(message) { UI.displayStatus(message, 'alert-info'); },

  success: function(message) { UI.displayStatus(message, 'alert-success'); },

  warning: function(message) { UI.displayStatus(message, 'alert-warning'); },

  apiError: function(response) {
    UI.error(((response && response.error) || 'error').toString());
  },

  ignoreError: function(response) {},

  dismissNotifications: function() { $('#status')
                                         .slideUp(); },

  bulkOperation: function(operation, onOperationFinished, options) {
    var isStopExecuted = false;
    var success = true;
    var error = null;

    var resolve = function(data) {};
    var reject = function(data) {
      success = false;
      error = data.error;
    };
    $('input[type=checkbox]')
        .each(function() {
          if (this.checked) {
            operation(this.id, resolve, reject);
          }
        });

    // Wait for all
    $(document)
        .ajaxStop(function() {
          if (!isStopExecuted) {
            // Make sure we execute this block once. onOperationFinish might
            // have
            // async calls that would fire ajaxStop event
            isStopExecuted = true;
            $(document).off('ajaxStop');

            onOperationFinished();

            if (success === false) {
              UI.error(UI.formatString(
                  options && options.errorTemplate || T.bulkOperationError,
                  error));
            } else {
              UI.success(T.updateItemsSuccess);
            }
          }
        });
  },

  prettyPrintJSON: function(json) {
    return UI.syntaxHighlight(JSON.stringify(json, undefined, 4) || '');
  },

  syntaxHighlight: function(json) {
    var jsonRE =
        /("(\\u[a-zA-Z0-9]{4}|\\[^u]|[^\\"])*"(\s*:)?|\b(true|false|null)\b|-?\d+(?:\.\d*)?(?:[eE][+\-]?\d+)?)/g;
    json =
        json.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
    return json.replace(jsonRE, function(match) {
      var cls = 'number';
      if (/^"/.test(match)) {
        if (/:$/.test(match)) {
          cls = 'key';
        } else {
          cls = 'string';
        }
      } else if (/true|false/.test(match)) {
        cls = 'boolean';
      } else if (/null/.test(match)) {
        cls = 'null';
      }
      return '<span class="' + cls + '">' + match + '</span>';
    });
  },

  columnName: function(idx) {
    var name = String.fromCharCode('A'.charCodeAt(0) + idx % 26);
    while (idx >= 26) {
      idx = (idx / 26) | 0;
      idx--;
      name = String.fromCharCode('A'.charCodeAt(0) + idx % 26) + name;
    }
    return name;
  },

  typeaheadWrapper: function(f) {
    let lastRequest = null;
    let pendingRequest = false;
    function wrappedCall(query, syncResults, asyncResults) {
      if (pendingRequest) {
        lastRequest = arguments;
        return;
      }
      pendingRequest = true;
      f({query: query})
          .then(data => asyncResults(data.results || data))
          .fail(UI.ignoreError)
          .always(() => {
            pendingRequest = false;

            // If there is a pending request, send it out now.
            if (!lastRequest) return;
            let currentRequest = lastRequest;
            lastRequest = null;
            wrappedCall(...currentRequest);
          });
    }
    return wrappedCall;
  },

  typeahead: function(elem, searchFn, cb) {
    cb = cb || function(event, val) { $(event.target).val(val.value); };
    elem.typeahead(
            {
              minLength: 2,
              highlight: true,
            },
            {
              source: UI.typeaheadWrapper(searchFn),
              async: true,
              display: 'label',
              templates: {
                suggestion: function(val) {
                  return UI.formatString(
                      '<div data-value="%(value)">%(label)</div>', val);
                },
              },
            })
        .on('typeahead:select', cb)
        .on('typeahead:autocomplete', cb)
        .trigger('change');
  },

  problemTypeahead: function(elem, cb) {
    cb = cb || function(event, val) { $(event.target).val(val.alias); };
    elem.typeahead(
            {
              minLength: 3,
              highlight: false,
            },
            {
              source: UI.typeaheadWrapper(API.Problem.list),
              async: true,
              display: 'alias',
              templates: {
                suggestion: function(val) {
                  return UI.formatString(
                      '<div data-value="%(alias)"><strong>%(title)</strong> (%(alias))</div>',
                      val);
                }
              }
            })
        .on('typeahead:select', cb)
        .on('typeahead:autocomplete', cb)
        .trigger('change');
  },

  problemContestTypeahead: function(elem, problemList, cb) {
    var substringMatcher = function(query, cb) {
      var matches, substringRegex;

      // an array that will be populated with substring matches
      matches = [];

      // regex used to determine if a string contains the substring `query`
      substringRegex = new RegExp(query, 'i');

      // iterate through the pool of strings and for any string that
      // contains the substring `query`, add it to the `matches` array
      $.each(problemList, function(i, problem) {
        if (substringRegex.test(problem.alias)) {
          matches.push(problem);
        }
      });

      cb(matches);
    };

    cb = cb || function(event, val) { $(event.target).val(val.alias); };

    elem.typeahead(
            {
              minLength: 3,
              highlight: false,
            },
            {
              source: substringMatcher,
              async: true,
              display: 'alias',
              templates: {
                suggestion: function(val) {
                  return UI.formatString(
                      '<div data-value="%(alias)">%(alias)</div>', val);
                },
              },
            })
        .on('typeahead:select', cb)
        .on('typeahead:autocomplete', cb);
  },

  schoolTypeahead: function(elem, cb) {
    cb = cb || function(event, val) { $(event.target).val(val.value); };
    elem.typeahead(
            {
              minLength: 2,
              highlight: true,
            },
            {
              source: UI.typeaheadWrapper(omegaup.API.School.list),
              async: true,
              display: 'label',
              templates: {
                empty: T.schoolToBeAdded,
                suggestion: function(val) {
                  return UI.formatString(
                      '<div data-value="%(value)">%(label)</div>', val);
                },
              }
            })
        .on('typeahead:select', cb)
        .on('typeahead:autocomplete', cb);
  },

  userTypeahead: function(elem, cb) { UI.typeahead(elem, API.User.list, cb); },

  groupTypeahead: function(elem, cb) {
    UI.typeahead(elem, API.Group.list, cb);
  },

  getProfileLink: function(username) {
    return '<a href="/profile/' + username + '" >' + username + '</a>';
  },

  toDDHHMM: function(duration) {
    var sec_num = parseInt(duration, 10);
    var days = Math.floor(sec_num / 86400);
    var hours = Math.floor((sec_num - (days * 86400)) / 3600);
    var minutes = Math.floor((sec_num - (days * 86400) - (hours * 3600)) / 60);
    var seconds = sec_num - (days * 86400) - (hours * 3600) - (minutes * 60);

    if (minutes < 10) {
      minutes = '0' + minutes;
    }
    if (seconds < 10) {
      seconds = '0' + seconds;
    }

    var time = '';
    if (days > 0) time += days + 'd ';
    return time + hours + 'h ' + minutes + 'm';
  },

  getFlag: function(country) {
    if (!country) {
      return '';
    }
    return ' <img src="/media/flags/' + country.toLowerCase() +
           '.png" width="16" height="11" title="' + country + '" />';
  },

  formatDateTime: function(date) {
    return date.format('{MM}/{dd}/{yyyy} {HH}:{mm}');
  },

  formatDate: function(date) { return date.format('{MM}/{dd}/{yyyy}'); },

  copyToClipboard: function(value) {
    let tempInput = document.createElement('textarea');

    tempInput.style = 'position: absolute; left: -1000px; top: -1000px';
    tempInput.value = value;

    document.body.appendChild(tempInput);

    try {
      tempInput.select();  // refactor-lint-disable
      document.execCommand('copy');
    } finally {
      document.body.removeChild(tempInput);
    }
  },

  renderSampleToClipboardButton: function() {
    document.querySelectorAll('.sample_io > tbody > tr > td:first-of-type')
        .forEach(function(item, index) {
          let inputValue = item.querySelector('pre').innerHTML;

          let clipboardButton = document.createElement('button');
          clipboardButton.title = T.copySampleCaseTooltip;
          clipboardButton.className = 'glyphicon glyphicon-copy clipboard';

          clipboardButton.addEventListener('click', function(event) {
            event.preventDefault();
            event.stopPropagation();
            UI.copyToClipboard(inputValue);
          });

          item.appendChild(clipboardButton);
        });
  },

  markdownConverter: function(options) {
    options = options || {};

    // Map of templates.
    var templates = {};
    if (options.preview) {
      templates['libinteractive:download'] =
          '<code class="libinteractive-download">' +
          '<i class="glyphicon glyphicon-download-alt"></i></code>';
    } else {
      templates['libinteractive:download'] =
          `<div class="libinteractive-download panel panel-default">
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

    let converter = Markdown.getSanitizingConverter();
    let whitelist = /^<\/?(a(?: (target|class|href)="[a-z/_-]+")*|code|i|table|tbody|thead|tr|th|td|div|h3|span|form(?: role="\w+")*|label|select|option(?: (value|selected)="\w+")*|strong|span|button(?: type="\w+")?)( class="[a-zA-Z0-9 _-]+")?>$/i;
    let imageWhitelist = new RegExp('^<img\\ssrc="data:image\/[a-zA-Z0-9/;,=+]+"(\\swidth="\\d{1,3}")?(\\sheight="\\d{1,3}")?(\\salt="[^"<>]*")?(\\stitle="[^"<>]*")?\\s?/?>$', 'i');

    converter.hooks.chain('isValidTag', function(tag) {
      return tag.match(whitelist) || tag.match(imageWhitelist);
    });

    converter.hooks.chain('postSpanGamut', function(text) {
      // Templates.
      text = text.replace(
          /^\s*\{\{([a-z0-9_:]+)\}\}\s*$/g, function(wholematch, m1) {
            if (templates.hasOwnProperty(m1)) {
              return templates[m1];
            }
            return '<strong style="color: red">Unrecognized template name: ' +
                   m1 + '</strong>';
          });
      // Images.
      let imageMapping = converter._imageMapping || options.imageMapping || {};
      text = text.replace(
          /<img src="([^"]+)" ([^>]+)>/g,
          function(wholeMatch, url, attributes) {
            if (url.indexOf('/') != -1 || !imageMapping.hasOwnProperty(url)) {
              return wholeMatch;
            }
            return '<img src="' + imageMapping[url] + '" ' + attributes + '>';
          });
      return text;
    });
    converter.hooks.chain('preBlockGamut', function(text, hashBlock) {
      // Sample I/O table.
      return text.replace(
          /^( {0,3}\|\| *input *\n(?:.|\n)+?\n) {0,3}\|\| *end *\n/gm,
          function(whole, inner) {
            var matches =
                inner.split(/ {0,3}\|\| *(input|output|description) *\n/);
            var result = '';
            var description_column = false;
            for (var i = 1; i < matches.length; i += 2) {
              if (matches[i] == 'description') {
                description_column = true;
                break;
              }
            }
            result += '<thead><tr>';
            result += '<th>Entrada</th>';
            result += '<th>Salida</th>';
            if (description_column) {
              result += '<th>Descripción</th>';
            }
            result += '</tr></thead>';
            var first_row = true;
            var columns = 0;
            result += '<tbody>';
            for (var i = 1; i < matches.length; i += 2) {
              if (matches[i] == 'description') {
                result += '<td>' + hashBlock(matches[i + 1]) + '</td>';
                columns++;
              } else {
                if (matches[i] == 'input') {
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
                result += '<td><pre>' + matches[i + 1].replace(/\s+$/, '') +
                          '</pre></td>';
                columns++;
              }
            }
            while (columns < (description_column ? 3 : 2)) {
              result += '<td></td>';
              columns++;
            }
            result += '</tr></tbody>';

            return hashBlock('<table class="sample_io">\n' + result +
                             '\n</table>');
          });
    });

    converter.makeHtmlWithImages = function(markdown, imageMapping) {
      try {
        converter._imageMapping = imageMapping;
        return converter.makeHtml(markdown);
      } finally {
        delete converter._imageMapping;
      }
    };

    return converter;
  },
};

export {UI as default};

$(document)
    .ajaxError(function(e, xhr, settings, exception) {
      if (xhr.status == 499 || xhr.readyState != 4) {
        // If we cancel the connection, let's just swallow the error since
        // the user is not going to see it.
        return;
      }
      try {
        var responseText = xhr.responseText;
        var response = {};
        if (responseText) {
          response = JSON.parse(responseText);
        }
        console.error(settings.url, xhr.status, response.error, response);
      } catch (e) {
        console.error(settings.url, xhr.status, xhr.responseText);
      }
    });
