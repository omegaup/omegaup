omegaup.OmegaUp.on('ready', function() {
  var arena = new omegaup.arena.Arena(
      omegaup.arena.GetOptionsFromLocation(window.location));
  Highcharts.setOptions({global: {useUTC: false}});
  omegaup.API.getAssignment(arena.options.courseAlias,
                            arena.options.assignmentAlias)
      .then(function(assignment) {
        assignment.start_time =
            omegaup.OmegaUp.time(assignment.start_time * 1000);
        assignment.finish_time =
            omegaup.OmegaUp.time(assignment.finish_time * 1000);
        arena.contestLoaded(assignment);
      });

  $(window).hashchange(arena.onHashChanged.bind(arena));
});
