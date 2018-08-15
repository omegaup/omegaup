$('#alert-close').on('click', function() { $('#status').slideUp(); });

$('#email-verification-alert-close')
    .on('click', function() { $('#email-verification-alert')
                                  .slideUp(); });

function isBrowserSupported() {
  if (typeof(window.history.replaceState) !== 'function') {
    return false;
  }
  if (typeof(window.WebSocket) !== 'function') {
    return false;
  }

  return true;
}

if (!isBrowserSupported()) {
  omegaup.UI.error(omegaup.T.unsupportedBrowser);
}
