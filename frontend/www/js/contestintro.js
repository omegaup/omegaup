omegaup.OmegaUp.on('ready', function() {
      var contestAlias =
          /\/arena\/([^\/]+)\/?/.exec(window.location.pathname)[1];
      var contestObject = null;

      $('#start-contest-form')
          .submit(function() {
            $('#request-access-form').hide();
            $('#start-contest-submit').prop('disabled', true);

            // Explicitly join the contest.
            omegaup.API.openContest(contestAlias, function(result) {
              if (result.status == 'error') {
                omegaup.UI.error(result.error);
                $('#start-contest-form').show();
              } else {
                window.location.reload();
              }
            });
            return false;
          });

      $('#request-access-form')
          .submit(function() {
            $('#request-access-form').hide();
            $('#request-access-submit').prop('disabled', true);
            omegaup.API.registerForContest(contestAlias, function(result) {
              if (result.status == 'error') {
                omegaup.UI.error(result.error);
                $('#request-access-form').show();
                $('#start-contest-submit').prop('disabled', false);
              } else {
                $('#registration_pending').removeClass('hidden');
              }
            });
            return false;
          });

      function formatDelta(delta) {
        var days = Math.floor(delta / (24 * 60 * 60 * 1000));
        delta -= days * (24 * 60 * 60 * 1000);
        var hours = Math.floor(delta / (60 * 60 * 1000));
        delta -= hours * (60 * 60 * 1000);
        var minutes = Math.floor(delta / (60 * 1000));
        delta -= minutes * (60 * 1000);
        var seconds = Math.floor(delta / 1000);

        var clock = '';

        if (days > 0) {
          clock += days + ':';
        }
        if (hours < 10) clock += '0';
        clock += hours + ':';
        if (minutes < 10) clock += '0';
        clock += minutes + ':';
        if (seconds < 10) clock += '0';
        clock += seconds;

        return clock;
      }

      function showCountdown() {
        var starttime = contestObject.start_time;
        var date = new Date().getTime();

        // we already know that date < starttime
        $('#countdown_clock').html(formatDelta(starttime.getTime() - (date)));
      }

      function readyToStart(contest) {
        // User is ready enter contest. If contest started,
        // show button, otherwise show countdown.
        var date = new Date().getTime();
        var clock = '';

        if (date > contest.finish_time.getTime()) {  // Ended
          $('#click-to-proceed').removeClass('hidden');
        } else if (date > contest.start_time.getTime()) {  // Started
          $('#click-to-proceed').removeClass('hidden');
        } else {  // Not started
          $('#ready-to-start').removeClass('hidden');
          contestObject = contest;
          setInterval(showCountdown.bind(), 1000);
        }
      }

      function contestLoaded(contest) {
        if (contest.status != 'ok') {
          $('#contest-details').hide();
          $('#contest-details')
              .parent()
              .removeClass('col-md-6')
              .addClass('col-md-2');
        } else {
          $('.contest #title').html(omegaup.UI.escape(contest.title));
          $('.contest #description')
              .html(omegaup.UI.escape(contest.description));

          $('.contest #time-until-start')
              .html(omegaup.UI.escape(contest.start_time));
          $('.contest #start-time').text(contest.start_time.long());
          $('.contest #finish-time').text(contest.finish_time.long());
          if (contest.show_scoreboard_after == 1) {
            $('.contest #show-scoreboard-after')
                .text(omegaup.T.contestNewFormScoreboardAtContestEnd);
          } else {
            $('.contest #show-scoreboard-after').hide();
          }
          if (contest.window_length != null) {
            $('.contest #window-length-enabled')
                .text(omegaup.UI.formatString(
                    omegaup.T.contestIntroDifferentStarts,
                    {window_length: contest.window_length}));
          } else {
            $('.contest #window-length-enabled').hide();
          }
          $('.contest #scoreboard')
              .text(omegaup.UI.formatString(
                  omegaup.T.contestIntroScoreboardTimePercent,
                  {window_length: contest.scoreboard}));
          $('.contest #submissions-gap')
              .text(omegaup.UI.formatString(
                  omegaup.T.contestIntroSubmissionsSeparationDesc,
                  {window_length: contest.submissions_gap / 60}));
          var penaltyTypes = {
            none: omegaup.T.contestNewFormNoPenalty,
            problem_open: omegaup.T.contestNewFormByProblem,
            contest_start: omegaup.T.contestNewFormByContests,
            runtime: omegaup.T.contestNewFormByRuntime
          };
          $('.contest #penalty-type').text(penaltyTypes[contest.penalty_type]);
          if (contest.penalty != 0) {
            $('.contest #penalty')
                .text(
                    omegaup.UI.formatString(omegaup.T.contestIntroPenaltyDesc,
                                            {window_length: contest.penalty}));
          } else {
            $('.contest #penalty').hide();
          }
          var feedbackTypes = {
            yes: omegaup.T.contestNewFormImmediateFeedbackDesc,
            no: '',
            partial: omegaup.T.contestNewFormImmediatePartialFeedbackDesc
          };
          $('.contest #feedback').text(feedbackTypes[contest.feedback]);
          if (contest.points_decay_factor != 0) {
            $('.contest #points-decay-factor')
                .text(omegaup.UI.formatString(
                    omegaup.T.contestNewFormDecrementFactor,
                    {window_length: contest.points_decay_factor}));
          } else {
            $('.contest #points-decay-factor').hide();
          }
        }

        // Feel free to re-write this if you have the time.
        if (contest.contestant_must_register) {
          if (contest.user_registration_requested) {
            if (contest.user_registration_answered) {
              if (contest.user_registration_accepted) {
                readyToStart(contest);
              } else {
                $('#registration_denied').removeClass('hidden');
              }
            } else {
              $('#registration_pending').removeClass('hidden');
            }
          } else {
            $('#must_register').removeClass('hidden');
          }
        } else {
          readyToStart(contest);
        }
        $('#intro-page').removeClass('hidden');
      }

      omegaup.API.getContestPublicDetails(contestAlias, contestLoaded);
    });
