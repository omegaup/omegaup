omegaup.OmegaUp.on('ready', function() {
  var contestAlias = /\/arena\/([^\/]+)\/?/.exec(window.location.pathname)[1];
  var contestObject = null;

  if ($('.requests-user-information').length) {
    var markdownConverter = omegaup.UI.markdownConverter();
    var payload = JSON.parse(document.getElementById('payload').innerText);
    document.getElementsByClassName('requests-user-information')[0].innerHTML =
        markdownConverter.makeHtml(payload['markdown']);
  }

  $('#start-contest-form')
      .on('submit', function(ev) {
        ev.preventDefault();
        $('#request-access-form').hide();
        $('#start-contest-submit').prop('disabled', true);
        var request = {
          contest_alias: contestAlias,
          share_user_information:
              $('input[name=share-user-information]:checked').val()
        };
        var userInformationRequest = {};
        if ($('.requests-user-information').length) {
          var gitObjectId = JSON.parse(
              document.getElementById('payload').innerText)['gitObjectId'];
          var statementType = JSON.parse(
              document.getElementById('payload').innerText)['statementType'];
          userInformationRequest = {
            privacy_git_object_id: gitObjectId,
            statement_type: statementType
          };
        }
        $.extend(request, userInformationRequest);

        // Explicitly join the contest.
        omegaup.API.Contest.open(request)
            .then(function(result) { window.location.reload(); })
            .fail(omegaup.UI.apiError);
      });

  $('input[name=share-user-information]')
      .on('click', function(ev) { enableStartContestButton(); });

  $('#request-access-form')
      .on('submit', function(ev) {
        ev.preventDefault();
        $('#request-access-form').hide();
        $('#request-access-submit').prop('disabled', true);
        omegaup.API.Contest.registerForContest({contest_alias: contestAlias})
            .then(function(result) {
              $('#registration_pending').removeClass('hidden');
            })
            .fail(function(result) {
              omegaup.UI.error(result.error);
              $('#request-access-form').show();
              $('#start-contest-submit').prop('disabled', false);
            });
      });

  function enableStartContestButton() {
    var $formElement = $('form#start-contest-form>p');
    if ($formElement.hasClass('basic-information-needed')) {
      return false;
    }
    if ($formElement.hasClass('requests-user-information-required') &&
        $('input[name=share-user-information]:checked').val() != '1') {
      $('#start-contest-submit').prop('disabled', true);
      return false;
    }
    $('#start-contest-submit').prop('disabled', false);
  }

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

    // we already know that now < starttime
    $('#countdown_clock').text(formatDelta(starttime.getTime() - Date.now()));
  }

  function readyToStart(contest) {
    // User is ready enter contest. If contest started,
    // show button, otherwise show countdown.
    var now = Date.now();
    var clock = '';

    if (now > contest.finish_time.getTime()) {  // Ended
      $('#click-to-proceed').removeClass('hidden');
    } else if (now > contest.start_time.getTime()) {  // Started
      $('#click-to-proceed').removeClass('hidden');
    } else {  // Not started
      $('#ready-to-start').removeClass('hidden');
      contestObject = contest;
      setInterval(showCountdown.bind(), 1000);
    }
  }

  omegaup.API.Contest.publicDetails({contest_alias: contestAlias})
      .then(function(contest) {
        $('.contest #title').text(omegaup.UI.contestTitle(contest));
        $('.contest #description').text(contest.description);

        $('.contest #time-until-start').text(contest.start_time);
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
              .text(omegaup.UI.formatString(omegaup.T.contestIntroPenaltyDesc,
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
        return contest;
      })
      .fail(function(contest) {
        $('#contest-details').hide();
        $('#contest-details')
            .parent()
            .removeClass('col-md-6')
            .addClass('col-md-2');
        return contest;
      })
      .always(function(contest) {
        $('#intro-page').removeClass('hidden');
        if (contest.admission_mode != 'registration') {
          readyToStart(contest);
          return;
        }
        if (!contest.user_registration_requested) {
          $('#must_register').removeClass('hidden');
          return;
        }
        if (!contest.user_registration_answered) {
          $('#registration_pending').removeClass('hidden');
          return;
        }
        if (!contest.user_registration_accepted) {
          $('#registration_denied').removeClass('hidden');
          return;
        }
        readyToStart(contest);
      });
});
