$('document')
    .ready(function() {
      $('.new_contest_form')
          .submit(function() {
            var window_length_value =
                $('#window_length_enabled').is(':checked') ?
                    $('#window_length').val() :
                    'NULL';

            omegaup.API.createContest(
                $('.new_contest_form #title').val(),
                $('.new_contest_form #description').val(),
                (new Date($('.new_contest_form #start_time').val()).getTime()) /
                    1000,
                (new Date($('.new_contest_form #finish_time').val())
                     .getTime()) /
                    1000,
                window_length_value, $('.new_contest_form #alias').val(),
                $('.new_contest_form #points_decay_factor').val(),
                $('.new_contest_form #submissions_gap').val() * 60,
                $('.new_contest_form #feedback').val(),
                $('.new_contest_form #penalty').val(), 0 /*public*/,
                $('.new_contest_form #scoreboard').val(),
                $('.new_contest_form #penalty_type').val(),
                $('.new_contest_form #show_scoreboard_after').val(),
                function(data) {
                  if (data.status == 'ok') {
                    window.location.replace(
                        '/contest/' + $('.new_contest_form #alias').val() +
                        '/edit/#problems');
                  } else {
                    omegaup.UI.error(data.error || 'error');
                  }
                });
            return false;
          });

      // Toggle on/off window length on checkbox change
      $('#window_length_enabled')
          .change(function() {
            if ($(this).is(':checked')) {
              // Enable
              $('#window_length').removeAttr('disabled');
            } else {
              // Disable
              $('#window_length').attr('disabled', 'disabled');
            }
          });
    });
