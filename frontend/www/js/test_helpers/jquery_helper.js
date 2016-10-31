(function jquery_helper() {
  'use strict';

  if(typeof window === 'undefined') {
    var jsdom = require('jsdom');
    var window = jsdom.jsdom().defaultView;
    global.document = window.document;

    var jQuery = require('jquery')(window);
    global.jQuery = global.$ = jQuery;
    global.omegaup = global.omegaup || {};
  }
})();
