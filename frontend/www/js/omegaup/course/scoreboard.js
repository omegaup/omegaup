import {Arena} from '../arena/arena.js';
import {API, UI, OmegaUp} from '../omegaup.js';

OmegaUp.on('ready', function() {
  let params =
      /\/course\/([^\/]+)\/assignment\/([^\/]+)\/scoreboard\/([^\/]+)\/?/.exec(
          window.location.pathname);

  let options = {
    // There is no UI to show clarifications with scoreboard-only views.
    disableClarifications: true,
    courseAlias: params[1],
    assignmentAlias: params[2],
    scoreboardToken: params[3],
  };
  let arena = new Arena(options);
  let getRankingByTokenRefresh = 5 * 60 * 1000;  // 5 minutes

  API.Course.getAssignment({
              course: arena.options.courseAlias,
              assignment: arena.options.assignmentAlias,
              token: arena.options.scoreboardToken,
            })
      .then(function(course) {
        arena.initProblems(course);
        arena.initClock(course.start_time, course.finish_time);
        arena.initProblemsetId(course);
        $('#title .course-title').text(course.name);

        API.Problemset.scoreboard({
                        problemset_id: arena.options.problemsetId,
                        token: arena.options.scoreboardToken
                      })
            .then(arena.rankingChange.bind(arena))
            .fail(UI.ignoreError);
        if (new Date() < course.finish_time && !arena.socket) {
          setInterval(function() {
            API.Problemset.scoreboard({
                            problemset_id: arena.options.problemsetId,
                            token: arena.options.scoreboardToken
                          })
                .then(arena.rankingChange.bind(arena))
                .fail(UI.ignoreError);
          }, getRankingByTokenRefresh);
        }

        $('#ranking').show();
        $('#root').fadeIn('slow');
        $('#loading').fadeOut('slow');
      })
      .fail(UI.apiError);
});
