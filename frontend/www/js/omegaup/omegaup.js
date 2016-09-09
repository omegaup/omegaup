var omegaup = omegaup || {};

omegaup.OmegaUp = {
	loggedIn: false,

	username: null,

	ready: false,

	_documentReady: false,

	_initialized: false,

	_deltaTime: undefined,

	_listeners: {
		'ready': [],
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
		omegaup.API.currentSession().then(function(data) {
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
		omegaup.API.time().then(function(data) {
			if (data.status != 'ok') return;
			omegaup.OmegaUp._deltaTime = data.time * 1000 - t0;
		});
	},

	_realTime: function(timestamp) {
		if (typeof(timestamp) === 'undefined') {
			return new Date().getTime();
		}
		return new Date(timestamp).getTime();
	},

	time: function(timestamp) {
		return new Date(omegaup.OmegaUp._realTime(timestamp) +
		                (omegaup.OmegaUp._deltaTime || 0));
	}
};

$(document).ready(omegaup.OmegaUp._onDocumentReady);
