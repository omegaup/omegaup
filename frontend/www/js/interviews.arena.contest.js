omegaup.OmegaUp.on('ready', function() {
  var arena = new omegaup.arena.Arena({
    contestAlias:
        /\/interview\/([^\/]+)\/arena/.exec(window.location.pathname)[1],
    isInterview: true
  });
  var admin = null;

  omegaup.API.Interview.details({interview_alias: arena.options.contestAlias})
      .then(arena.contestLoaded.bind(arena))
      .fail(omegaup.UI.apiError);

  $(window).hashchange(arena.onHashChanged.bind(arena));
});
