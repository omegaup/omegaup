omegaup.OmegaUp.on('ready', function() {
  $('.new_contest_form').on('submit', function(ev) {
    ev.preventDefault();
    var window_length_value = $('#window-length-enabled').is(':checked')
      ? $('#window-length').val()
      : '0';

    omegaup.API.Contest.create({
      title: $('.new_contest_form #title').val(),
      description: $('.new_contest_form #description').val(),
      start_time:
        new Date($('.new_contest_form #start-time').val()).getTime() / 1000,
      finish_time:
        new Date($('.new_contest_form #finish-time').val()).getTime() / 1000,
      window_length: window_length_value,
      alias: $('.new_contest_form #alias').val(),
      points_decay_factor: $('.new_contest_form #points-decay-factor').val(),
      submissions_gap: $('.new_contest_form #submissions-gap').val() * 60,
      feedback: $('.new_contest_form #feedback').val(),
      penalty: $('.new_contest_form #penalty').val(),
      public: 0,
      scoreboard: $('.new_contest_form #scoreboard').val(),
      penalty_type: $('.new_contest_form #penalty-type').val(),
      show_scoreboard_after: $(
        '.new_contest_form #show-scoreboard-after',
      ).val(),
      languages: $('.new_contest_form #languages').val(),
      basic_information: $('.new_contest_form #basic-information-required').is(
        ':checked',
      ),
      requests_user_information: $(
        '.new_contest_form #requests-user-information',
      ).val(),
    })
      .then(function(data) {
        window.location.replace(
          '/contest/' + $('.new_contest_form #alias').val() + '/edit/#problems',
        );
      })
      .catch(omegaup.UI.apiError);
    return false;
  });

  // Toggle on/off window length on checkbox change
  $('#window-length-enabled').on('change', function() {
    if ($(this).is(':checked')) {
      // Enable
      $('#window-length').prop('disabled', false);
    } else {
      // Disable
      $('#window-length').attr('disabled', 'disabled');
    }
  });
});
