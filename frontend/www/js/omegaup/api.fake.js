(function() {
  // We shouldn't be communicating with the real API.
  $.realAjax = $.ajax;
  $.ajax = function() {
    console.error('Unexpected Ajax call');
    var dfd = $.Deferred();
    dfd.reject({ status: 'error' });
    return dfd.promise();
  };

  // TODO(lhchavez): This is currently needed in omegaup.OmegaUp._initialize(),
  // which is unconditionally executed when omegaup.js finishes loading. That
  // call should be removed at some point, but until that happens, let's
  // unconditionally mock the session to be always active.
  omegaup.API.Session.currentSession = () => {
    return new Promise((accept, reject) => {
      accept({
        status: 'ok',
        session: {
          valid: true,
          user: { username: 'omegaup' },
          email: 'omegaup@omegaup.com',
        },
      });
    });
  };
})();
