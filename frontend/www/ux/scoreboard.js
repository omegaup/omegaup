omegaup.OmegaUp.on('ready', function() {
  var params = /\/arena\/([^\/]+)\/scoreboard\/([^\/]+)\/?/.exec(
      window.location.pathname);
  var options = {
    // There is no UI to show clarifications with scoreboard-only views.
    disableClarifications: true,
    contestAlias: params[1],
    scoreboardToken: params[2],
  };
  var arena = new omegaup.arena.Arena(options);
  var getRankingByTokenRefresh = 5 * 60 * 1000;  // 5 minutes
  omegaup.API.Contest.details({
                       contest_alias: arena.options.contestAlias,
                       token: arena.options.scoreboardToken,
                     })
      .then(function(contest) {
        arena.initProblems(contest);
        arena.initClock(contest.start_time, contest.finish_time);
        arena.initProblemsetId(contest);
        $('#title .contest-title').text(omegaup.UI.contestTitle(contest));
        omegaup.API.Problemset.scoreboard({
                                problemset_id: arena.options.problemsetId,
                                token: arena.options.scoreboardToken
                              })
            .then(arena.rankingChange.bind(arena))
            .fail(omegaup.UI.ignoreError);
        if (new Date() < contest.finish_time && !arena.socket) {
          setInterval(function() {
            omegaup.API.Problemset.scoreboard({
                                    problemset_id: arena.options.problemsetId,
                                    token: arena.options.scoreboardToken
                                  })
                .then(arena.rankingChange.bind(arena))
                .fail(omegaup.UI.ignoreError);
          }, getRankingByTokenRefresh);
        }

        $('#ranking').show();
        $('#root').fadeIn('slow');
        $('#loading').fadeOut('slow');
      })
      .fail(omegaup.UI.apiError);
});
