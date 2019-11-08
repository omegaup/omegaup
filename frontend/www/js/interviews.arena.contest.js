omegaup.OmegaUp.on('ready', function() {
  var arena = new omegaup.arena.Arena({
    contestAlias: /\/interview\/([^\/]+)\/arena/.exec(
      window.location.pathname,
    )[1],
    isInterview: true,
  });
  var admin = null;

  omegaup.API.Interview.details({ interview_alias: arena.options.contestAlias })
    .then(arena.problemsetLoaded.bind(arena))
    .fail(omegaup.UI.apiError);

  window.addEventListener('hashchange', arena.onHashChanged.bind(arena));
});
