omegaup.OmegaUp.on('ready', function() {
  var params = /\/arena\/([^\/]+)\/scoreboard\/([^\/]+)\/?/.exec(
    window.location.pathname,
  );
  var options = {
    // There is no UI to show clarifications with scoreboard-only views.
    disableClarifications: true,
    contestAlias: params[1],
    scoreboardToken: params[2],
  };
  var arenaInstance = new arena.Arena(options);
  var getRankingByTokenRefresh = 5 * 60 * 1000; // 5 minutes
  omegaup.API.Contest.details({
    contest_alias: arenaInstance.options.contestAlias,
    token: arenaInstance.options.scoreboardToken,
  })
    .then(function(contest) {
      arenaInstance.initProblemsetId(contest);
      arenaInstance.initProblems(contest);
      arenaInstance.initClock(contest.start_time, contest.finish_time);
      $('#title .contest-title').text(omegaup.UI.contestTitle(contest));
      omegaup.API.Problemset.scoreboard({
        problemset_id: arenaInstance.options.problemsetId,
        token: arenaInstance.options.scoreboardToken,
      })
        .then(arenaInstance.rankingChange.bind(arenaInstance))
        .fail(omegaup.UI.ignoreError);
      if (new Date() < contest.finish_time && !arenaInstance.socket) {
        setInterval(function() {
          omegaup.API.Problemset.scoreboard({
            problemset_id: arenaInstance.options.problemsetId,
            token: arenaInstance.options.scoreboardToken,
          })
            .then(arenaInstance.rankingChange.bind(arenaInstance))
            .fail(omegaup.UI.ignoreError);
        }, getRankingByTokenRefresh);
      }

      $('#ranking').show();
      $('#root').fadeIn('slow');
      $('#loading').fadeOut('slow');
    })
    .fail(omegaup.UI.apiError);
});
