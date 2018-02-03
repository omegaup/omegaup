// This is a permanent hook that sends any uncaught errors in
// jQuery.Deferred()s to the server.
jQuery.Deferred.exceptionHook = function(error, stack) {
  sendError(error.message, error.filename, error.lineno, error.colno, error);
};

// This is a temporary hook that will be enabled until we are ready to remove
// the jQuery Migration library.
jQuery.migrateWarnHook = function(msg) {
  try {
    throw new Error(msg);
  } catch (error) {
    sendError(msg, error.filename, error.lineno, error.colno, error);
  }
};
