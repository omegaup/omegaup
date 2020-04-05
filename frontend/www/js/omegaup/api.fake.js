(function() {
  'use strict';

  if (typeof window === 'undefined') {
    const jsdom = require('jsdom');
    const { JSDOM } = jsdom;
    const { window } = new JSDOM('').window;
    global.document = window.document;

    var jQuery = require('jquery')(window);
    global.jQuery = global.$ = jQuery;

    // window and navigator objects are required by typeahead.jquery.js
    global.window = window;
    global.navigator = window.navigator;

    require('../../third_party/js/typeahead.jquery.js');
  }

  // We shouldn't be communicating with the real API.
  window.fetch = function(url, options) {
    if (url.toLowerCase() == '/api/session/currentsession/') {
      // TODO(lhchavez): This is currently needed in omegaup.OmegaUp._initialize(),
      // which is unconditionally executed when omegaup.js finishes loading. That
      // call should be removed at some point, but until that happens, let's
      // unconditionally mock the session to be always active.
      return Promise.resolve({
        ok: true,
        status: 200,
        json: function() {
          return {
            status: 'ok',
            session: {
              valid: true,
              identity: { username: 'omegaup' },
              user: { username: 'omegaup' },
              email: 'omegaup@omegaup.com',
            },
          };
        },
      });
    }
    console.error('Unexpected Ajax call', arguments);
    throw new Exception('Unexpected Ajax call: ' + arguments);
  };

  if (typeof global !== 'undefined') {
    global.fetch = window.fetch;
  }
})();
