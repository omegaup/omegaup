$('#alert-close').on('click', function() { $('#status').slideUp(); });

$('#email-verification-alert-close')
    .on('click', function() { $('#email-verification-alert')
                                  .slideUp(); });

function isBrowserSupported() {
  if (typeof(window.history.replaceState) !== 'function') {
    return false;
  }

  return true;
}

if (!isBrowserSupported()) {
  omegaup.UI.error(
      "The browser doesn't support all the features of this page. Please try to update.");
}
