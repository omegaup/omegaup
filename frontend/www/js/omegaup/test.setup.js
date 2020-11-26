const util = require('util');
const process = require('process');

require('jest-fetch-mock').enableMocks();
fetchMock.mockIf(/^\/api\/.*/, req => {
  if (req.url != '/api/session/currentSession/') {
    return Promise.resolve({
      ok: false,
      status: 404,
      body: JSON.stringify({
        status: 'error',
        error: `Invalid call to "${req.url}" in test`,
        errorcode: 403,
      }),
    });
  }
  return Promise.resolve({
    status: 200,
    body: JSON.stringify({
      status: 'ok',
      session: {
        valid: false,
      },
      time: Date.now() / 1000,
    }),
  });
});

global.jQuery = require('jquery');
global.$ = global.jQuery;
window.jQuery = global.jQuery;

// This is needed for CodeMirror to work.
global.document.createRange = () => {
  return {
    setEnd: () => {},
    setStart: () => {},
    getBoundingClientRect: () => {},
    getClientRects: () => [],
  };
};

// Any write to console.error() will cause a test failure.
const originalConsoleError = console.error;
console.error = function() {
  originalConsoleError(...arguments);
  throw new Error(
    'Unexpected call to console.error(). Failing test: ' +
      util.inspect(arguments),
  );
};

// Make sure that warnings will not cause test termination. This is because
// warnings are always emitted in the next tick, which will cause an unhandled
// exception and kill the node process altogether.
process.removeAllListeners('warning');
process.on('warning', warning => {
  originalConsoleError(warning.stack);
});

// https://github.com/vuejs/vue-test-utils/issues/936
window.Date = Date;
