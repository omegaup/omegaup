if (typeof require === 'undefined') {
  window.require = function(name) {
    if (name.endsWith('omegaup.js')) {
      return omegaup;
    }
    // do nothing
    // allows jasmine specs to require a node module on node
    // without failing on the browser
  };
}
