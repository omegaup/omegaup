omegaup.OmegaUp.on('ready', function() {
  var interviewAlias = /\/interview\/([^\/]+)\/result\/([^\/]+)?.*/.exec(
    window.location.pathname
  )[1];
  var candidateUsername = /\/interview\/([^\/]+)\/result\/([^\/]+)?.*/.exec(
    window.location.pathname
  )[2];

  omegaup.API.getInterviewStatsForUser(
    interviewAlias,
    candidateUsername,
    function(userStats) {
      $('.page-header h1 span').html(
        omegaup.T['interviewResultsFor'] + ' ' + userStats.name_or_username
      );

      var subtitleHtml = 'Interview url : ' + userStats.interview_url;

      $('.page-header h3 small').html(subtitleHtml);

      if (userStats.finished) {
        $('.page-header h1 small').html(omegaup.T['interviewFinished']);
      } else if (userStats.opened_interview) {
        $('.page-header h1 small').html(omegaup.T['interviewInProgress']);
      } else {
        $('.page-header h1 small').html(omegaup.T['interviewNotStarted']);
      }
    }
  );

  omegaup.API.getRuns(
    { username: candidateUsername, contest_alias: interviewAlias },
    function(runs) {
      console.log(runs);
    }
  );
});
