omegaup.OmegaUp.on('ready', function() {
  var contestAlias =
      /\/interview\/([^\/]+)\/arena?/.exec(window.location.pathname)[1];
  var contestObject = null;

  $('#start-contest-form')
      .submit(function(ev) {
        ev.preventDefault();
        $('#request-access-form').hide();
        $('#start-contest-submit').prop('disabled', true);

        // Explicitly join the contest.
        omegaup.API.Contest.open(contestAlias)
            .then(function(result) { window.location.reload(); })
            .fail(function(result) {
              omegaup.UI.error(result.error);
              $('#start-contest-form').show();
            });
      });

  $('#request-access-form')
      .submit(function(ev) {
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
    var date = Date.now();

    // we already know that date < starttime
    $('#countdown_clock').html(formatDelta(starttime.getTime() - (date)));
  }

  function readyToStart(contest) {
    // User is ready enter contest. If contest started,
    // show button, otherwise show countdown.
    var date = Date.now();
    var clock = '';

    if (date > contest.finish_time.getTime()) {  // Ended
      $('#click_to_proceed').removeClass('hidden');
    } else if (date > contest.start_time.getTime()) {  // Started
      $('#click_to_proceed').removeClass('hidden');
    } else {  // Not started
      $('#ready_to_start').removeClass('hidden');
      contestObject = contest;
      setInterval(showCountdown.bind(), 1000);
    }
  }

  omegaup.API.Contest.publicDetails({contest_alias: contestAlias})
      .then(function(contest) {
        $('.contest #title').html(omegaup.UI.escape(contest.title));
        $('.contest #description').html(omegaup.UI.escape(contest.description));
        $('.contest #window_length').val(contest.window_length);
        readyToStart(contest);
      })
      .fail(function(contest) {
        $('#contest-details').hide();
        $('#contest-details')
            .parent()
            .removeClass('col-md-6')
            .addClass('col-md-2');
      });
});
