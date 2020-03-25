omegaup.OmegaUp.on('ready', function() {
  var arenaInstance = new arena.Arena({
    contestAlias: /\/interview\/([^\/]+)\/arena/.exec(
      window.location.pathname,
    )[1],
    isInterview: true,
  });

  omegaup.API.Interview.details({
    interview_alias: arenaInstance.options.contestAlias,
  })
    .then(arenaInstance.problemsetLoaded.bind(arenaInstance))
    .catch(omegaup.UI.apiError);

  window.addEventListener(
    'hashchange',
    arenaInstance.onHashChanged.bind(arenaInstance),
  );
});
