omegaup.OmegaUp.on('ready', function() {
  var options = omegaup.arena.GetOptionsFromLocation(window.location);
  var assignmentMatch = /\/course\/([^\/]+)(?:\/assignment\/([^\/]+)\/?)?/.exec(
      window.location.pathname);
  if (assignmentMatch) {
    options.courseAlias = assignmentMatch[1];
    options.assignmentAlias = assignmentMatch[2];
  }

  var arena = new omegaup.arena.Arena(options);
  Highcharts.setOptions({global: {useUTC: false}});
  omegaup.API.getAssignment({
               course: arena.options.courseAlias,
               assignment: arena.options.assignmentAlias
             })
      .then(arena.contestLoaded.bind(arena));

  $(window).hashchange(arena.onHashChanged.bind(arena));
});
