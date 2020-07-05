// This is a permanent hook that sends any uncaught errors in
// jQuery.Deferred()s to the server.
jQuery.Deferred.exceptionHook = function (error, stack) {
  sendError(error.message, error.filename, error.lineno, error.colno, error);
};
