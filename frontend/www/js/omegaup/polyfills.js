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

if (window.Element && !Element.prototype.closest) {
  Element.prototype.closest = function(s) {
    var matches = (this.document || this.ownerDocument).querySelectorAll(s), i,
        el = this;
    do {
      i = matches.length;
      while (--i >= 0 && matches.item(i) !== el) {
      }
    } while ((i < 0) && (el = el.parentElement));
    return el;
  };
}
