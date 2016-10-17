$(document)
    .ready(function() {
      var contestAlias =
          /\/interview\/([^\/]+)\/arena?/.exec(window.location.pathname)[1];
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
        if (hours < 10)
          clock += '0';
        clock += hours + ':';
        if (minutes < 10)
          clock += '0';
        clock += minutes + ':';
        if (seconds < 10)
          clock += '0';
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

        if (date > contest.finish_time.getTime()) { // Ended
          $('#click_to_proceed').removeClass('hidden');
        } else if (date > contest.start_time.getTime()) { // Started
          $('#click_to_proceed').removeClass('hidden');
        } else { // Not started
          $('#ready_to_start').removeClass('hidden');
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
          $('.contest #window_length').val(contest.window_length);
          readyToStart(contest);
        }
      }

      omegaup.API.getContestPublicDetails(contestAlias, contestLoaded);
    });
