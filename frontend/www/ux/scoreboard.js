omegaup.OmegaUp.on('ready', function() {
  var params =
      /\/arena\/([^\/]+)\/problemset_id\/([^\/]+)\/scoreboard\/([^\/]+)\/?/
          .exec(window.location.pathname);
  var options = {
    // There is no UI to show clarifications with scoreboard-only views.
    disableClarifications: true,
    contestAlias: params[1],
    problemsetId: params[2],
    scoreboardToken: params[3],
  };
  var arena = new omegaup.arena.Arena(options);
  var getRankingByTokenRefresh = 5 * 60 * 1000;  // 5 minutes
  omegaup.API.Problemset.details({
                          contest_alias: arena.options.contestAlias,
                          problemset_id: arena.options.problemsetId,
                          token: arena.options.scoreboardToken,
                        })
      .then(function(contest) {
        arena.initProblems(contest);
        arena.initClock(contest.start_time, contest.finish_time);
        $('#title .contest-title').text(contest.title);

        omegaup.API.Problemset.scoreboard({
                                contest_alias: arena.options.contestAlias,
                                problemset_id: arena.options.problemsetId,
                                token: arena.options.scoreboardToken
                              })
            .then(arena.rankingChange.bind(arena))
            .fail(omegaup.UI.ignoreError);
        if (new Date() < contest.finish_time && !arena.socket) {
          setInterval(function() {
            omegaup.API.Problemset.scoreboard({
                                    contest_alias: arena.options.contestAlias,
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
