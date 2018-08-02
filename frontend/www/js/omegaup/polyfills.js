// This file contains miscellaneous polyfills to increase
// backwards-compatibility with older browsers.

'use strict';

// From https://developer.mozilla.org/en-US/docs/Web/API/NodeList/forEach
if (window.NodeList && !window.NodeList.prototype.forEach) {
  window.NodeList.prototype.forEach = function(callback, thisArg) {
    thisArg = thisArg || window;
    for (var i = 0; i < this.length; i++) {
      callback.call(thisArg, this[i], i, this);
    }
  };
}

if (window.Node && !window.Node.prototype.innerText && Object.defineProperty) {
  Object.defineProperty(window.Node.prototype, 'innerText', {
    get: function() { return this.textContent; },
    set: function(value) { this.textContent = value; },
  });
}

// From
// https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/String/includes#Polyfill
if (!String.prototype.includes) {
  String.prototype.includes = function(search, start) {
    if (typeof start !== 'number') {
      start = 0;
    }

    if (start + search.length > this.length) {
      return false;
    } else {
      return this.indexOf(search, start) !== -1;
    }
  };
}
