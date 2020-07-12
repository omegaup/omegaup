$('#alert-close').on('click', omegaup.UI.dismissNotifications);

function isBrowserSupported() {
  // From
  // https://developer.mozilla.org/en-US/docs/Web/HTTP/Browser_detection_using_the_user_agent#Browser_Name
  if (
    navigator.userAgent.indexOf('Safari/') !== -1 &&
    navigator.userAgent.indexOf('Chrome/') === -1
  ) {
    return false;
  }
  if (typeof window.history.replaceState !== 'function') {
    return false;
  }
  if (typeof window.WebSocket !== 'function') {
    return false;
  }
  if (typeof FileReader == 'undefined') {
    return false;
  }
  return true;
}

if (!isBrowserSupported()) {
  omegaup.UI.error(omegaup.T.unsupportedBrowser);
}
