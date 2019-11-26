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
    get: function() {
      return this.textContent;
    },
    set: function(value) {
      this.textContent = value;
    },
  });
}

// From https://developer.mozilla.org/en-US/docs/Web/API/Element/closest
if (window.Element && !window.Element.prototype.matches)
  window.Element.prototype.matches =
    window.Element.prototype.msMatchesSelector ||
    window.Element.prototype.webkitMatchesSelector;

if (window.Element && !window.Element.prototype.closest) {
  window.Element.prototype.closest = function(s) {
    var el = this;
    if (!document.documentElement.contains(el)) return null;
    do {
      if (el.matches(s)) return el;
      el = el.parentElement || el.parentNode;
    } while (el !== null && el.nodeType === 1);
    return null;
  };
}
