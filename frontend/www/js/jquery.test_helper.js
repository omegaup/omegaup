(function jquery_helper() {
  'use strict';

  if (typeof window === 'undefined') {
    const jsdom = require('jsdom');
    const { JSDOM } = jsdom;
    const { window } = new JSDOM('').window;
    global.document = window.document;

    var jQuery = require('jquery')(window);
    global.jQuery = global.$ = jQuery;

    var ko = require('../third_party/js/knockout-3.5.0beta.js');
    global.ko = ko;

    // window and navigator objects are required by typeahead.jquery.js
    global.window = window;
    global.navigator = window.navigator;

    require('../third_party/js/knockout-secure-binding.min.js');
    require('../third_party/js/typeahead.jquery.js');
  }
})();
