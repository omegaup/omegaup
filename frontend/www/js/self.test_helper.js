(function self_helper() {
  'use strict';

  if (typeof self === 'undefined') {
    global.self = {};
  }
})();
