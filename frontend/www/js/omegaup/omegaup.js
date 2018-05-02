import API from './api.js';
import UI from './ui.js';
import * as arena from './arena/arena.js';

export {API, UI, arena};

// This is the JavaScript version of the frontend's Experiments class.
export class Experiments {
  constructor(experimentList) {
    var self = this;
    self.enabledExperiments = {};
    if (!experimentList) return;
    for (var i = 0; i < experimentList.length; i++)
      self.enabledExperiments[experimentList[i]] = true;
  }

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

// Stub for translations.
// These should be loaded later with OmegaUp.loadTranslations.
export let T = {};

export let OmegaUp = {
  loggedIn: false,

      username: null,

      ready: false,

      experiments: null,

      _documentReady: false,

      _initialized: false,

      _remoteDeltaTime: undefined,

      _deltaTimeForTesting: 0,

      _listeners:
          {
            'ready': [
              function() { OmegaUp.experiments = Experiments.loadGlobal(); },
              function() {
                ko.bindingProvider.instance =
                    new ko.secureBindingsProvider({attribute: 'data-bind'});
              }
            ],
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
                    OmegaUp.username = data.session.user.username;
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
            for (var i = 0; i < OmegaUp._listeners[eventName].length; i++) {
              OmegaUp._listeners[eventName][i]();
            }
            OmegaUp._listeners[eventName] = [];
          },

      loadTranslations:
          function(t) {
            for (var p in t) {
              if (!t.hasOwnProperty(p)) {
                continue;
              }
              T[p] = t[p];
            }
          },

      on:
          function(events, handler) {
            OmegaUp._initialize();
            var splitNames = events.split(' ');
            for (var i = 0; i < splitNames.length; i++) {
              if (!OmegaUp._listeners.hasOwnProperty(splitNames[i])) continue;

              if (splitNames[i] == 'ready' && OmegaUp.ready) {
                handler();
                continue;
              }

              OmegaUp._listeners[splitNames[i]].push(handler);
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

      convertTimes: function(item) {
        if (item.hasOwnProperty('time')) {
          item.time = OmegaUp.remoteTime(item.time * 1000);
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
};

if (document.readyState === 'complete' ||
    (document.readyState !== 'loading' && !document.documentElement.doScroll)) {
  OmegaUp._onDocumentReady();
} else {
  document.addEventListener('DOMContentLoaded', OmegaUp._onDocumentReady);
}
