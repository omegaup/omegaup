import { Arena } from '../arena/arena.js';
import { OmegaUp } from '../omegaup';
import * as api from '../api';
import * as UI from '../ui';

OmegaUp.on('ready', function() {
  const payload = JSON.parse(
    document.getElementById('header-payload').innerText,
  );
  let params = /\/course\/([^\/]+)\/assignment\/([^\/]+)\/scoreboard\/([^\/]+)\/?/.exec(
    window.location.pathname,
  );

  let options = {
    // There is no UI to show clarifications with scoreboard-only views.
    disableClarifications: true,
    courseAlias: params[1],
    assignmentAlias: params[2],
    scoreboardToken: params[3],
    payload: payload,
  };
  let arena = new Arena(options);
  let getRankingByTokenRefresh = 5 * 60 * 1000; // 5 minutes

  api.Course.assignmentDetails({
    course: arena.options.courseAlias,
    assignment: arena.options.assignmentAlias,
    token: arena.options.scoreboardToken,
  })
    .then(function(course) {
      arena.initProblemsetId(course);
      arena.initProblems(course);
      arena.initClock(course.start_time, course.finish_time);
      $('#title .course-title').text(course.name);

      api.Problemset.scoreboard({
        problemset_id: arena.options.problemsetId,
        token: arena.options.scoreboardToken,
      })
        .then(arena.rankingChange.bind(arena))
        .catch(UI.ignoreError);
      if (new Date() < course.finish_time && !arena.socket) {
        setInterval(function() {
          api.Problemset.scoreboard({
            problemset_id: arena.options.problemsetId,
            token: arena.options.scoreboardToken,
          })
            .then(arena.rankingChange.bind(arena))
            .catch(UI.ignoreError);
        }, getRankingByTokenRefresh);
      }

      $('#ranking').show();
      $('#root').fadeIn('slow');
      $('#loading').fadeOut('slow');
    })
    .catch(UI.apiError);
});
