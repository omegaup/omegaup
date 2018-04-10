omegaup.OmegaUp.on('ready', function() {
  var options = omegaup.arena.GetOptionsFromLocation(window.location);
  var assignmentMatch =
      /\/course\/([^\/]+)(?:\/assignment\/([^\/]+))(?:\/problemset_id\/([^\/]+)\/?)?/
          .exec(window.location.pathname);
  if (assignmentMatch) {
    options.courseAlias = assignmentMatch[1];
    options.assignmentAlias = assignmentMatch[2];
    options.problemsetId = assignmentMatch[3];
  }

  var arena = new omegaup.arena.Arena(options);
  Highcharts.setOptions({global: {useUTC: false}});
  omegaup.API.Course.getAssignment({
                      course: arena.options.courseAlias,
                      assignment: arena.options.assignmentAlias
                    })
      .then(arena.contestLoaded.bind(arena))
      .fail(omegaup.UI.apiError);

  window.addEventListener('hashchange', arena.onHashChanged.bind(arena));
});
