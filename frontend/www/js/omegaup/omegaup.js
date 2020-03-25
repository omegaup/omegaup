import { Experiments, EventListenerList, OmegaUp, T, UI } from './omegaup.ts';
import API from './api.js';
export { API, EventListenerList, Experiments, OmegaUp, T, UI };

OmegaUp.on('ready', function() {
  // ko.secureBindingsProvider.nodeHasBindings() has a bug in which if
  // there happens to be a comment with no content (like `<!---->`), it
  // tries to call .trim() on undefined, and crashes.
  ko.secureBindingsProvider.prototype.nodeHasBindings = function(node) {
    if (node.nodeType === node.ELEMENT_NODE) {
      return (
        node.getAttribute(this.attribute) ||
        (ko.components && ko.components.getComponentNameForNode(node))
      );
    }
    if (node.nodeType === node.COMMENT_NODE) {
      if (this.noVirtualElements) {
        return false;
      }
      // Ensures that `value` is not undefined.
      let value = '' + node.nodeValue || node.text;
      if (!value) {
        return false;
      }
      // See also: knockout/src/virtualElements.js
      return value.trim().indexOf('ko ') === 0;
    }
  };
  ko.bindingProvider.instance = new ko.secureBindingsProvider({
    attribute: 'data-bind',
  });
});

if (
  document.readyState === 'complete' ||
  (document.readyState !== 'loading' && !document.documentElement.doScroll)
) {
  OmegaUp._onDocumentReady();
} else {
  document.addEventListener(
    'DOMContentLoaded',
    OmegaUp._onDocumentReady.bind(OmegaUp),
  );
}
