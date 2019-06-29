omegaup.OmegaUp.on('ready', function() {
  var payload = JSON.parse(document.getElementById('payload').innerText);
  var options = omegaup.arena.GetOptionsFromLocation(window.location);
  var assignmentMatch = /\/course\/([^\/]+)(?:\/assignment\/([^\/]+)\/?)?/.exec(
      window.location.pathname);
  if (assignmentMatch) {
    options.courseAlias = assignmentMatch[1];
    options.assignmentAlias = assignmentMatch[2];
    options.showWarning = payload.shouldShowFirstAssociatedIdentityRunWarning;
  }

  if (options.showWarning) {
    omegaup.UI.warning(omegaup.T.firstSumbissionWithIdentity);
  }

  var arena = new omegaup.arena.Arena(options);
  Highcharts.setOptions({global: {useUTC: false}});
  omegaup.API.Course.getAssignment({
                      course: arena.options.courseAlias,
                      assignment: arena.options.assignmentAlias
                    })
      .then(arena.problemsetLoaded.bind(arena))
      .fail(omegaup.UI.apiError);

  window.addEventListener('hashchange', arena.onHashChanged.bind(arena));
});
