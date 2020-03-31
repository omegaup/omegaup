(function() {
  // We shouldn't be communicating with the real API.
  window.fetch = function(url, options) {
    if (url.toLowerCase() == '/api/session/currentsession/') {
      // TODO(lhchavez): This is currently needed in omegaup.OmegaUp._initialize(),
      // which is unconditionally executed when omegaup.js finishes loading. That
      // call should be removed at some point, but until that happens, let's
      // unconditionally mock the session to be always active.
      return Promise.resolve({
        status: 'ok',
        session: {
          valid: true,
          user: { username: 'omegaup' },
          email: 'omegaup@omegaup.com',
        },
      });
    }
    console.error('Unexpected Ajax call', arguments);
    throw new Exception('Unexpected Ajax call: ' + arguments);
  };
})();
