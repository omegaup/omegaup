var omegaup = typeof global === 'undefined' ?
                  (window.omegaup = window.omegaup || {}) :
                  (global.omegaup = global.omegaup || {});

// This is the JavaScript version of the frontend's Experiments class.
omegaup.Experiments = function(experimentList) {
  var self = this;
  self.enabledExperiments = {};
  if (!experimentList) return;
  for (var i = 0; i < experimentList.length; i++)
    self.enabledExperiments[experimentList[i]] = true;
};

// Current frontend-available experiments:
omegaup.Experiments.SCHOOLS = 'schools';

// The list of all enabled experiments for a particular request should have
// been injected into the DOM by Smarty.
omegaup.Experiments.loadGlobal = function() {
  var experimentsNode = $('#omegaup-enabled-experiments');
  var experimentsList = null;
  if (experimentsNode.length == 1)
    experimentsList = experimentsNode[0].innerText.split(',');
  return new omegaup.Experiments(experimentsList);
};

omegaup.Experiments.prototype.isEnabled = function(name) {
  var self = this;
  return self.enabledExperiments.hasOwnProperty(name);
};

omegaup.OmegaUp = {
  loggedIn: false,

  username: null,

  ready: false,

  experiments: null,

  _documentReady: false,

  _initialized: false,

  _deltaTime: undefined,

  _listeners: {
    'ready': [
      function() {
        omegaup.OmegaUp.experiments = omegaup.Experiments.loadGlobal();
      },
      function() {
        ko.bindingProvider.instance =
            new ko.secureBindingsProvider({attribute: 'data-bind'});
      }
    ],
  },

  _onDocumentReady: function() {
    omegaup.OmegaUp._documentReady = true;
    if (typeof(omegaup.OmegaUp._deltaTime) !== 'undefined') {
      omegaup.OmegaUp._notify('ready');
    }
    // TODO(lhchavez): Remove this.
    omegaup.OmegaUp._initialize();
  },

  _initialize: function() {
    var t0 = new Date().getTime();
    omegaup.API.Session.currentSession().then(function(data) {
      if (data.session.valid) {
        omegaup.OmegaUp.loggedIn = true;
        omegaup.OmegaUp._deltaTime = data.time * 1000 - t0;
        omegaup.OmegaUp.username = data.session.user.username;
        omegaup.OmegaUp.email = data.session.email;
      }

      omegaup.OmegaUp.ready = true;
      if (omegaup.OmegaUp._documentReady) {
        omegaup.OmegaUp._notify('ready');
      }
    });
  },

  _notify: function(eventName) {
    for (var i = 0; i < omegaup.OmegaUp._listeners[eventName].length; i++) {
      omegaup.OmegaUp._listeners[eventName][i]();
    }
    omegaup.OmegaUp._listeners[eventName] = [];
  },

  on: function(events, handler) {
    if (omegaup.OmegaUp._initialized) return;
    omegaup.OmegaUp._initialize();
    var splitNames = events.split(' ');
    for (var i = 0; i < splitNames.length; i++) {
      if (!omegaup.OmegaUp._listeners.hasOwnProperty(splitNames[i])) continue;

      if (splitNames[i] == 'ready' && omegaup.OmegaUp.ready) {
        handler();
        return;
      }

      omegaup.OmegaUp._listeners[splitNames[i]].push(handler);
    }
  },

  syncTime: function() {
    var t0 = new Date().getTime();
    omegaup.API.Time.get().then(function(data) {
      omegaup.OmegaUp._deltaTime = data.time * 1000 - t0;
    });
  },

  _realTime: function(timestamp) {
    if (typeof(timestamp) === 'undefined') {
      return new Date().getTime();
    }
    return new Date(timestamp).getTime();
  },

  time: function(timestamp, options) {
    options = options ||  {};
    options.server_sync = (typeof(options.server_sync) === 'undefined') ?
                              true :
                              options.server_sync;
    return new Date(
        omegaup.OmegaUp._realTime(timestamp) +
        (options.server_sync ? (omegaup.OmegaUp._deltaTime || 0) : 0));
  }
};

$(document).ready(omegaup.OmegaUp._onDocumentReady);
