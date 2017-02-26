omegaup.OmegaUp.on('ready', function() {
  var arena = new omegaup.arena.Arena({
    contestAlias:
        /\/interview\/([^\/]+)\/arena/.exec(window.location.pathname)[1],
    isInterview: true
  });
  var admin = null;

  omegaup.API.getInterview(arena.options.contestAlias,
                           arena.contestLoaded.bind(arena));

  $(window).hashchange(arena.onHashChanged.bind(arena));
});
