omegaup.OmegaUp.on('ready', function() {
      $('.new_contest_form')
          .submit(function() {
            var window_length_value =
                $('#window-length-enabled').is(':checked') ?
                    $('#window-length').val() :
                    'NULL';

            omegaup.API.createContest(
                $('.new_contest_form #title').val(),
                $('.new_contest_form #description').val(),
                (new Date($('.new_contest_form #start-time').val()).getTime()) /
                    1000,
                (new Date($('.new_contest_form #finish-time').val())
                     .getTime()) /
                    1000,
                window_length_value, $('.new_contest_form #alias').val(),
                $('.new_contest_form #points-decay-factor').val(),
                $('.new_contest_form #submissions-gap').val() * 60,
                $('.new_contest_form #feedback').val(),
                $('.new_contest_form #penalty').val(), 0 /*public*/,
                $('.new_contest_form #scoreboard').val(),
                $('.new_contest_form #penalty-type').val(),
                $('.new_contest_form #show-scoreboard-after').val(),
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
      $('#window-length-enabled')
          .change(function() {
            if ($(this).is(':checked')) {
              // Enable
              $('#window-length').removeAttr('disabled');
            } else {
              // Disable
              $('#window-length').attr('disabled', 'disabled');
            }
          });
    });
