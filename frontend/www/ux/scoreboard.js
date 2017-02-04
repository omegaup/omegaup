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

  arena.connectSocket();
  omegaup.API.getContestByToken({
               contest_alias: arena.options.contestAlias,
               token: arena.options.scoreboardToken,
             })
      .then(function(contest) {
        arena.initProblems(contest);
        arena.initClock(contest.start_time, contest.finish_time);
        $('#title .contest-title').html(contest.title);

        omegaup.API.getRankingByToken(arena.options.contestAlias,
                                      arena.options.scoreboardToken,
                                      arena.rankingChange.bind(arena));
        if (omegaup.OmegaUp.time() < contest.finish_time && !arena.socket) {
          setInterval(function() {
            omegaup.API.getRankingByToken(arena.options.contestAlias,
                                          arena.options.scoreboardToken,
                                          arena.rankingChange.bind(arena));
          }, getRankingByTokenRefresh);
        }

        $('#ranking').show();
        $('#root').fadeIn('slow');
        $('#loading').fadeOut('slow');
      });
});
