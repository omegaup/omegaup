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

  // Current frontend-available experiments:
  static get SCHOOLS() { return 'schools'; }

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

      _deltaTime: undefined,

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
            if (typeof(OmegaUp._deltaTime) !== 'undefined') {
              OmegaUp._notify('ready');
            }
            // TODO(lhchavez): Remove this.
            OmegaUp._initialize();
          },

      _initialize:
          function() {
            var t0 = new Date().getTime();
            API.Session.currentSession()
                .then(function(data) {
                  if (data.session.valid) {
                    OmegaUp.loggedIn = true;
                    OmegaUp._deltaTime = data.time * 1000 - t0;
                    OmegaUp.username = data.session.user.username;
                    OmegaUp.email = data.session.email;
                  }

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
            if (OmegaUp._initialized) return;
            OmegaUp._initialize();
            var splitNames = events.split(' ');
            for (var i = 0; i < splitNames.length; i++) {
              if (!OmegaUp._listeners.hasOwnProperty(splitNames[i])) continue;

              if (splitNames[i] == 'ready' && OmegaUp.ready) {
                handler();
                return;
              }

              OmegaUp._listeners[splitNames[i]].push(handler);
            }
          },

      syncTime:
          function() {
            var t0 = new Date().getTime();
            API.Time.get()
                .then(function(data) {
                  OmegaUp._deltaTime = data.time * 1000 - t0;
                })
                .fail(UI.apiError);
          },

      _realTime:
          function(timestamp) {
            if (typeof(timestamp) === 'undefined') {
              return new Date().getTime();
            }
            return new Date(timestamp).getTime();
          },

      time:
          function(timestamp, options) {
            options = options ||  {};
            options.server_sync =
                (typeof(options.server_sync) === 'undefined') ?
                    true :
                    options.server_sync;
            return new Date(
                OmegaUp._realTime(timestamp) +
                (options.server_sync ? (OmegaUp._deltaTime || 0) : 0));
          },

      convertTimes: function(item) {
        if (item.hasOwnProperty('start_time')) {
          item.start_time = OmegaUp.time(item.start_time * 1000);
        }
        if (item.hasOwnProperty('finish_time')) {
          item.finish_time = OmegaUp.time(item.finish_time * 1000);
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
