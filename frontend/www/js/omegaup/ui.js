import API from './api.js';

let UI = {
  navigateTo: function(url) { window.location = url; },

  escape: function(s) {
    if (typeof s !== 'string') return '';
    return s.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
  },

  formatString: function(template, values) {
    for (var key in values) {
      if (!values.hasOwnProperty(key)) continue;
      template =
          template.replace(new RegExp('%\\(' + key + '\\)', 'g'), values[key]);
    }
    return template;
  },

  displayStatus: function(message, type) {
    if ($('#status .message').length == 0) {
      console.error('Showing warning but there is no status div');
    }

    $('#status .message').html(message);
    $('#status')
        .removeClass('alert-success alert-info alert-warning alert-danger')
        .addClass(type)
        .slideDown();
  },

  error: function(message) { UI.displayStatus(message, 'alert-danger'); },

  info: function(message) { UI.displayStatus(message, 'alert-info'); },

  success: function(message) { UI.displayStatus(message, 'alert-success'); },

  warning: function(message) { UI.displayStatus(message, 'alert-warning'); },

  apiError: function(response) { UI.error(response.error || 'error'); },

  dismissNotifications: function() { $('#status')
                                         .slideUp(); },

  bulkOperation: function(operation, onOperationFinished) {
    var isStopExecuted = false;
    var success = true;
    var error = null;

    handleResponseCallback = function(data) {
      if (data.status !== 'ok') {
        success = false;
        error = data.error;
      }
    };
    $('input[type=checkbox]')
        .each(function() {
          if (this.checked) {
            operation(this.id, handleResponseCallback);
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
              UI.error('Error actualizando items: ' + error);
            } else {
              UI.success('Todos los items han sido actualizados');
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

  typeaheadWrapper: function(f) {
    var lastRequest = null;
    var pending = false;
    function wrappedCall(query, callback) {
      if (pending) {
        lastRequest = [query, callback];
      } else {
        pending = true;
        f(query, function(data) {
          if (lastRequest != null) {
            // Typeahead will ignore any stale callbacks. Given that we
            // will start a new request ASAP, let's do a best-effort
            // callback to the current request with the old data.
            lastRequest[1](data);
          } else {
            callback(data);
          }
          pending = false;
          if (lastRequest != null) {
            var request = lastRequest;
            lastRequest = null;
            wrappedCall(request[0], request[1]);
          }
        });
      }
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
              displayKey: 'label',
            })
        .on('typeahead:selected', cb);
  },

  problemTypeahead: function(elem) {
    elem.typeahead(
            {
              minLength: 3,
              highlight: false,
            },
            {
              source: function(query, cb) {
                API.Problem.list({query: query})
                    .then(function(data) { cb(data.results); });
              },
              displayKey: 'alias',
              templates: {
                suggestion: function(val) {
                  return UI.formatString('<strong>%(title)</strong> (%(alias))',
                                         val);
                }
              }
            })
        .on('typeahead:selected', function(event, val) {
          $(event.target).val(val.alias);
        });
  },

  userTypeahead: function(elem, cb) {
    UI.typeahead(elem, API.searchUsers, cb);
  },

  getProfileLink: function(username) {
    return '<a href="/profile/' + username + '" >' + username + '</a>';
  },

  // From
  // http://stackoverflow.com/questions/6312993/javascript-seconds-to-time-with-format-hhmmss
  toHHMM: function(duration) {
    var sec_num = parseInt(duration, 10);
    var hours = Math.floor(sec_num / 3600);
    var minutes = Math.floor((sec_num - (hours * 3600)) / 60);
    var seconds = sec_num - (hours * 3600) - (minutes * 60);

    if (minutes < 10) {
      minutes = '0' + minutes;
    }
    if (seconds < 10) {
      seconds = '0' + seconds;
    }

    var time = hours + 'h ' + minutes + 'm';
    return time;
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

  formatDate: function(date) { return date.format('{MM}/{dd}/{yyyy}'); }
};

export {UI as default};

$(document)
    .ajaxError(function(e, xhr, settings, exception) {
      try {
        var response = jQuery.parseJSON(xhr.responseText);
        console.error(settings.url, xhr.status, response.error, response);
      } catch (e) {
        console.error(settings.url, xhr.status, xhr.responseText);
      }
    });
