require('jsdom-global')(undefined, {
  pretendToBeVisual: true,
  url: 'http://localhost',
});
global.jQuery = require('jquery');
global.$ = global.jQuery;

// https://github.com/vuejs/vue-test-utils/issues/936
window.Date = Date;
