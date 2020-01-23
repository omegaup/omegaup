omegaup.OmegaUp.on('ready', function() {
  omegaup.UI.problemTypeahead($('#problem-search-box'), function(event, item) {
    window.location.href =
      window.location.origin + '/arena/problem/' + item.alias;
  });
});
