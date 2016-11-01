(function jquery_helper() {
  'use strict';

  if (typeof window === 'undefined') {
    var jsdom = require('jsdom');
    var window = jsdom.jsdom().defaultView;
    global.document = window.document;

    var jQuery = require('jquery')(window);
    global.jQuery = global.$ = jQuery;

    var ko = require('../../third_party/js/knockout-4.3.0.js');
    global.ko = ko;

    // window and navigator objects are required by typeahead.jquery.js
    global.window = window;
    var navigator = {userAgent: 'node-js', platform: 'Linux i686'};
    global.window.navigator = global.navigator = navigator;
    navigator.platform = 'Linux i686';

    require('../../third_party/js/knockout-secure-binding.min.js');
    require('../../third_party/js/typeahead.jquery.js');
  }
})();
