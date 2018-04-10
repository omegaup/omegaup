omegaup.OmegaUp.on('ready', function() {
  var params =
      /\/course\/([^\/]+)\/assignment\/([^\/]+)\/scoreboard\/([^\/]+)\/?/.exec(
          window.location.pathname);

  var options = {
    // There is no UI to show clarifications with scoreboard-only views.
    disableClarifications: true,
    courseAlias: params[1],
    assignmentAlias: params[2],
    scoreboardToken: params[3],
  };
  var arena = new omegaup.arena.Arena(options);
  var getRankingByTokenRefresh = 5 * 60 * 1000;  // 5 minutes

  omegaup.API.Course.getAssignment({
                      course: arena.options.courseAlias,
                      assignment: arena.options.assignmentAlias,
                      token: arena.options.scoreboardToken,
                    })
      .then(function(course) {
        arena.initProblems(course);
        arena.initClock(course.start_time, course.finish_time);
        $('#title .course-title').text(course.name);

        omegaup.API.Course.assignmentScoreboard({
                            course_alias: arena.options.courseAlias,
                            assignment_alias: arena.options.assignmentAlias,
                            token: arena.options.scoreboardToken
                          })
            .then(arena.rankingCourseChange.bind(arena))
            .fail(omegaup.UI.ignoreError);
        if (new Date() < course.finish_time && !arena.socket) {
          setInterval(function() {
            omegaup.API.Course.assignmentScoreboard({
                                course_alias: arena.options.courseAlias,
                                assignment_alias: arena.options.assignmentAlias,
                                token: arena.options.scoreboardToken
                              })
                .then(arena.rankingCourseChange.bind(arena))
                .fail(omegaup.UI.ignoreError);
          }, getRankingByTokenRefresh);
        }

        $('#ranking').show();
        $('#root').fadeIn('slow');
        $('#loading').fadeOut('slow');
      })
      .fail(omegaup.UI.apiError);
});
