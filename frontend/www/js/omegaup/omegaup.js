import API from './api.js';
import UI from './ui.js';
import * as arena from './arena/arena.js';
import * as lang_en from './lang.en.js';
import * as lang_es from './lang.es.js';
import * as lang_pt from './lang.pt.js';
import * as lang_pseudo from './lang.pseudo.js';

export {API, UI, arena};

// This is the JavaScript version of the frontend's Experiments class.
export class Experiments {
  constructor(experimentList) {
    var self = this;
    self.enabledExperiments = {};
    if (!experimentList) return;
    for (let experiment of experimentList)
      self.enabledExperiments[experiment] = true;
  }

  // Current frontend-available experiments:
  static get IDENTITIES() { return 'identities'; }

  // The list of all enabled experiments for a particular request should have
  // been injected into the DOM by Smarty.
  static loadGlobal() {
    var experimentsNode =
        document.getElementById('omegaup-enabled-experiments');
    var experimentsList = null;
    if (experimentsNode) experimentsList = experimentsNode.innerText.split(',');
    return new Experiments(experimentsList);
  }

  isEnabled(name) {
    var self = this;
    return self.enabledExperiments.hasOwnProperty(name);
  }
}
;

// Holds event listeners and notifies them exactly once. An event listener that
// is added after the .notify() method has been called will be notified
// immediately without adding it to the list.
class EventListenerList {
  constructor(listenerList) {
    var self = this;
    self.listenerList = [];
    self.ready = false;
    if (!listenerList) return;
    for (let listener of listenerList) self.listenerList.push(listener);
  }

  notify() {
    var self = this;
    self.ready = true;
    for (let listener of self.listenerList) listener();
    self.listenerList = [];
  }

  add(listener) {
    var self = this;
    if (self.ready) {
      listener();
      return;
    }

    self.listenerList.push(listener);
  }
}
;

// Translation strings.
export let T = (function() {
  const head =
      (document && document.querySelector && document.querySelector('head')) ||
      null;

  switch ((head && head.dataset && head.dataset.locale) || 'es') {
    case 'pseudo':
      return lang_pseudo.default;

    case 'pt':
      return lang_pt.default;

    case 'en':
      return lang_en.default;

    case 'es':
    default:
      return lang_es.default;
  }
})();

export let OmegaUp = {
  loggedIn: false,

      username: null,

      ready: false,

      experiments: null,

      _documentReady: false,

      _initialized: false,

      _remoteDeltaTime: undefined,

      _deltaTimeForTesting: 0,

      _errors:[],

      _listeners:
          {
            'ready': new EventListenerList([
              function() { OmegaUp.experiments = Experiments.loadGlobal(); },
              function() {
                ko.bindingProvider.instance =
                    new ko.secureBindingsProvider({attribute: 'data-bind'});
              },
              function() {
                let reportAnIssue = document.getElementById('report-an-issue');
                if (!reportAnIssue || !window.navigator ||
                    !window.navigator.userAgent || !T.reportAnIssueTemplate) {
                  return;
                }
                reportAnIssue.addEventListener('click', function(event) {
                  // Not using UI.formatString() to avoid creating a circular
                  // dependency.
                  let issueBody =
                      T.reportAnIssueTemplate.replace(
                                                 '%(userAgent)',
                                                 window.navigator.userAgent)
                          .replace('%(referer)', window.location.href)
                          .replace('%(serializedErrors)',
                                   JSON.stringify(OmegaUp._errors))
                          .replace(/\\n/g, '\n');
                  reportAnIssue.href =
                      'https://github.com/omegaup/omegaup/issues/new?body=' +
                      encodeURIComponent(issueBody);
                });
              },
            ]),
          },

      _onDocumentReady:
          function() {
            OmegaUp._documentReady = true;
            if (OmegaUp.ready) {
              OmegaUp._notify('ready');
              return;
            }
            // TODO(lhchavez): Remove this.
            OmegaUp._initialize();
          },

      _initialize:
          function() {
            if (OmegaUp._initialized) {
              return;
            }
            OmegaUp._initialized = true;
            var t0 = OmegaUp._realTime();
            API.Session.currentSession()
                .then(function(data) {
                  if (data.session.valid) {
                    OmegaUp.loggedIn = true;
                    OmegaUp.username = data.session.identity.username;
                    OmegaUp.identity = data.session.identity;
                    OmegaUp.email = data.session.email;
                  }
                  OmegaUp._remoteDeltaTime = t0 - data.time * 1000;

                  OmegaUp.ready = true;
                  if (OmegaUp._documentReady) {
                    OmegaUp._notify('ready');
                  }
                })
                .fail(UI.apiError);
          },

      _notify:
          function(eventName) {
            if (!OmegaUp._listeners.hasOwnProperty(eventName)) return;
            OmegaUp._listeners[eventName].notify();
          },

      on:
          function(events, handler) {
            OmegaUp._initialize();
            for (let eventName of events.split(' ')) {
              if (!OmegaUp._listeners.hasOwnProperty(eventName)) continue;
              OmegaUp._listeners[eventName].add(handler);
            }
          },

      _realTime:
          function(timestamp) {
            if (typeof(timestamp) !== 'undefined') {
              return timestamp + OmegaUp._deltaTimeForTesting;
            }
            return Date.now() + OmegaUp._deltaTimeForTesting;
          },

      remoteTime:
          function(timestamp, options) {
            options = options ||  {};
            options.server_sync =
                (typeof(options.server_sync) === 'undefined') ?
                    true :
                    options.server_sync;
            return new Date(
                OmegaUp._realTime(timestamp) +
                (options.server_sync ? (OmegaUp._remoteDeltaTime || 0) : 0));
          },

      convertTimes:
          function(item) {
            if (item.hasOwnProperty('time')) {
              item.time = OmegaUp.remoteTime(item.time * 1000);
            }
            if (item.hasOwnProperty('end_time')) {
              item.end_time = OmegaUp.remoteTime(item.end_time * 1000);
            }
            if (item.hasOwnProperty('start_time')) {
              item.start_time = OmegaUp.remoteTime(item.start_time * 1000);
            }
            if (item.hasOwnProperty('finish_time')) {
              item.finish_time = OmegaUp.remoteTime(item.finish_time * 1000);
            }
            if (item.hasOwnProperty('last_updated')) {
              item.last_updated = OmegaUp.remoteTime(item.last_updated * 1000);
            }
            if (item.hasOwnProperty('submission_deadline')) {
              item.submission_deadline =
                  OmegaUp.remoteTime(item.submission_deadline * 1000);
            }
            return item;
          },

      addError: function(error) { OmegaUp._errors.push(error); },
};

if (document.readyState === 'complete' ||
    (document.readyState !== 'loading' && !document.documentElement.doScroll)) {
  OmegaUp._onDocumentReady();
} else {
  document.addEventListener('DOMContentLoaded', OmegaUp._onDocumentReady);
}
