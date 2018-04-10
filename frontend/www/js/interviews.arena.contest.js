omegaup.OmegaUp.on('ready', function() {
  var assignmentMatch =
      /\/interview\/([^\/]+)(?:\/problemset_id\/([^\/]+)\/?)\/arena/.exec(
          window.location.pathname);
  var arena = new omegaup.arena.Arena({
    contestAlias: assignmentMatch[1],
    problemsetId: assignmentMatch[2],
    isInterview: true
  });
  var admin = null;

  omegaup.API.Interview.details({interview_alias: arena.options.contestAlias})
      .then(arena.contestLoaded.bind(arena))
      .fail(omegaup.UI.apiError);

  window.addEventListener('hashchange', arena.onHashChanged.bind(arena));
});
