const util = require('util');

require('jsdom-global')(undefined, {
  pretendToBeVisual: true,
  url: 'http://localhost',
});
global.jQuery = require('jquery');
global.$ = global.jQuery;

console.error = function() {
  throw new Error(util.inspect(...arguments));
};

// https://github.com/vuejs/vue-test-utils/issues/936
window.Date = Date;
