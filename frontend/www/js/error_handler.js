// This should not use jQuery (or any other non-vanilla JS) and should work
// with as many old browsers (IE9+) as possible.
function sendError(message, filename, lineno, colno, error) {
  'use strict';
  // Try to get a stack.
  try {
    if (!error) {
      error = new Error('force-added error');
    }
    if (!error.stack) {
      error.stack = new Error('force-added stack').stack;
      if (error.stack) {
        error.stack = error.stack.toString();
      } else {
        try {
          throw new Error('force-thrown stack');
        } catch (e) {
          error.stack = e.stack;
        }
      }
    }
  } catch (e) {
    // That was a best-effort attempt to grab a stack. Older browsers will
    // still fail, but at the very least the filename and line number should be
    // correctly reported.
  }

  // Try to add it to the list of errors.
  try {
    omegaup.OmegaUp.addError(error);
  } catch (e) {
    // Something went wrong. Let's keep trying to send the error.
    console.error('Failed to add the error to the error list', e);
  }

  // Try to send the error.
  try {
    var httpRequest = new XMLHttpRequest();
    httpRequest.onreadystatechange = function () {
      if (httpRequest.readyState !== XMLHttpRequest.DONE) return;
      if (httpRequest.status === 200) return;

      // The submission failed.
      console.error('Failed to upload the error', httpRequest.status);
    };
    httpRequest.open('POST', '/jserrorreport.php');
    httpRequest.setRequestHeader('Content-Type', 'application/jserror-report');
    var report = {
      userAgent: navigator.userAgent,
      location: window.location.href,
    };
    if (error.stack) report.stack = error.stack;
    if (typeof message !== 'undefined') report.message = message;
    if (typeof filename !== 'undefined') report.filename = filename;
    if (typeof lineno !== 'undefined') report.lineno = lineno;
    if (typeof colno !== 'undefined') report.colno = colno;
    httpRequest.send(JSON.stringify(report));
  } catch (e) {
    console.error('Failed to upload the error', e);
  }
  console.error('Unhandled exception ', error);
}
if (window.addEventListener) {
  // Way too old browsers (IE8) don't even support window.addEventListener.
  // Let's stop making it worse for them and just silently degrade.
  window.addEventListener('error', function (event) {
    'use strict';
    sendError(
      event.message,
      event.filename,
      event.lineno,
      event.colno,
      event.error,
    );
    return true;
  });
}
