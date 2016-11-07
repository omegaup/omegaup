if (typeof require === 'undefined') {
  window.require = function() {
    // do nothing
    // allows jasmine specs to require a node module on node
    // without failing on the browser
  };
}
