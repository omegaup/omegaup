$('#alert-close').on('click', function() { $('#status').slideUp(); });

$('#email-verification-alert-close')
    .on('click', function() { $('#email-verification-alert')
                                  .slideUp(); });

function isBrowserSupported() {
  if (navigator.userAgent.indexOf('Safari') !== -1 &&
      navigator.userAgent.indexOf('Chrome') === -1) {
    return false;
  }
  if (typeof(window.history.replaceState) !== 'function') {
    return false;
  }
  if (typeof(window.WebSocket) !== 'function') {
    return false;
  }
  if (typeof(FileReader) == 'undefined') {
    return false;
  }
  return true;
}

if (!isBrowserSupported()) {
  omegaup.UI.error(omegaup.T.unsupportedBrowser);
}
