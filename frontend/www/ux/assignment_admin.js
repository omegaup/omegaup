omegaup.OmegaUp.on('ready', function() {
  var options = omegaup.arena.GetOptionsFromLocation(window.location);
  var assignmentMatch = /\/course\/([^\/]+)(?:\/assignment\/([^\/]+)\/?)?/.exec(
      window.location.pathname);
  if (assignmentMatch) {
    options.courseAlias = assignmentMatch[1];
    options.assignmentAlias = assignmentMatch[2];
  }

  var arena = new omegaup.arena.Arena(options);
  var admin = new omegaup.arena.ArenaAdmin(arena);
  admin.refreshRuns();

  // Trigger the event (useful on page load).
  arena.onHashChanged();

  $('#loading').fadeOut('slow');
  $('#root').fadeIn('slow');

  Highcharts.setOptions({global: {useUTC: false}});
  omegaup.API.Course.getAssignment({
                      course: arena.options.courseAlias,
                      assignment: arena.options.assignmentAlias
                    })
      .then(arena.problemsetLoaded.bind(arena))
      .fail(omegaup.UI.apiError);

  window.addEventListener('hashchange', arena.onHashChanged.bind(arena));
});
