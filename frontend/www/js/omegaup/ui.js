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
    UI.error((response.error || 'error').toString());
  },

  ignoreError: function(response) {},

  dismissNotifications: function() { $('#status')
                                         .slideUp(); },

  bulkOperation: function(operation, onOperationFinished) {
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
        f({query: query})
            .then(function(data) {
              if (lastRequest != null) {
                // Typeahead will ignore any stale callbacks. Given that we
                // will start a new request ASAP, let's do a best-effort
                // callback to the current request with the old data.
                lastRequest[1](data);
                pending = false;
                var request = lastRequest;
                lastRequest = null;
                wrappedCall(request[0], request[1]);
              } else {
                if (data.results) {
                  callback(data.results);
                } else {
                  callback(data);
                }
              }
            })
            .fail(UI.ignoreError)
            .always(function() { pending = false; });
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
        .on('typeahead:selected', cb)
        .on('typeahead:autocompleted', cb);
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
              displayKey: 'alias',
              templates: {
                suggestion: function(val) {
                  return UI.formatString('<strong>%(title)</strong> (%(alias))',
                                         val);
                }
              }
            })
        .on('typeahead:selected', cb)
        .on('typeahead:autocompleted', cb);
  },

  userTypeahead: function(elem, cb) { UI.typeahead(elem, API.User.list, cb); },

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
