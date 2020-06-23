const util = require('util');

require('jsdom-global')(undefined, {
  pretendToBeVisual: true,
  url: 'http://localhost',
});
global.jQuery = require('jquery');
global.$ = global.jQuery;

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
  throw new Error('Unexpected call to console.error(). Failing test.');
};

// https://github.com/vuejs/vue-test-utils/issues/936
window.Date = Date;
