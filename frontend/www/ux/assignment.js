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
  omegaup.API.Course.getAssignment({
                      course: arena.options.courseAlias,
                      assignment: arena.options.assignmentAlias
                    })
      .then(arena.problemsetLoaded.bind(arena))
      .fail(omegaup.UI.apiError);

  window.addEventListener('hashchange', arena.onHashChanged.bind(arena));

  $('#submissions-help')
      .on('click', function(e) {
        introJs()
            .setOption('doneLabel', 'Next page')
            .start()
            .oncomplete(function() {
              window.location.href =
                  window.location + '/new-run?multipage=true';
            });
        return false;
      });
});
