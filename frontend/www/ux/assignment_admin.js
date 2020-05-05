omegaup.OmegaUp.on('ready', function() {
  var options = arena.GetOptionsFromLocation(window.location);
  var assignmentMatch = /\/course\/([^\/]+)(?:\/assignment\/([^\/]+)\/?)?/.exec(
    window.location.pathname,
  );
  if (assignmentMatch) {
    options.courseAlias = assignmentMatch[1];
    options.assignmentAlias = assignmentMatch[2];
  }

  var arenaInstance = new arena.Arena(options);
  var adminInstance = new arena.ArenaAdmin(arenaInstance);
  adminInstance.refreshRuns();

  // Trigger the event (useful on page load).
  arenaInstance.onHashChanged();

  $('#loading').fadeOut('slow');
  $('#root').fadeIn('slow');

  Highcharts.setOptions({ global: { useUTC: false } });
  omegaup.API.Course.assignmentDetails({
    course: arenaInstance.options.courseAlias,
    assignment: arenaInstance.options.assignmentAlias,
  })
    .then(arenaInstance.problemsetLoaded.bind(arenaInstance))
    .catch(omegaup.UI.apiError);

  window.addEventListener(
    'hashchange',
    arenaInstance.onHashChanged.bind(arenaInstance),
  );
});
