$('document')
    .ready(function() {
      $('#start_time, #finish_time')
          .datetimepicker({
            weekStart: 1,
            format: 'mm/dd/yyyy hh:ii',
            startDate: Date.create(Date.now()),
          });

      if ($('#start_time').val() == '') {
        // Defaults for start_time and end_time
        var defaultDate = Date.create(Date.now());
        defaultDate.set({seconds: 0});
        $('#start_time').val(omegaup.UI.formatDateTime(defaultDate));
        defaultDate.setHours(defaultDate.getHours() + 5);
        $('#finish_time').val(omegaup.UI.formatDateTime(defaultDate));
      }

      $('#window_length_enabled')
          .change(function() {
            if ($(this).is(':checked')) {
              $('#window_length').removeAttr('disabled');
            } else {
              $('#window_length').attr('disabled', 'disabled');
            }
          });

      // Defaults for OMI
      $('#omi')
          .click(function() {
            $('.new_contest_form #title')
                .attr('placeholder',
                      omegaup.T.contestNewFormTitlePlaceholderOmiStyle);
            $('#window_length_enabled').removeAttr('checked');
            $('#window_length').attr('disabled', 'disabled');
            $('#window_length').val('');

            $('.new_contest_form #scoreboard').val('0');
            $('.new_contest_form #points_decay_factor').val('0');
            $('.new_contest_form #submissions_gap').val('1');
            $('.new_contest_form #feedback').val('yes');
            $('.new_contest_form #penalty').val('0');
            $('.new_contest_form #penalty_type').val('none');
            $('.new_contest_form #show_scoreboard_after').val('1');
          });

      // Defaults for preselectivos IOI
      $('#preioi')
          .click(function() {
            $('.new_contest_form #title')
                .attr('placeholder',
                      omegaup.T.contestNewFormTitlePlaceholderIoiStyle);
            $('#window_length_enabled').attr('checked', 'checked');
            $('#window_length').removeAttr('disabled');
            $('#window_length').val('180');

            $('.new_contest_form #scoreboard').val('0');
            $('.new_contest_form #points_decay_factor').val('0');
            $('.new_contest_form #submissions_gap').val('0');
            $('.new_contest_form #feedback').val('yes');
            $('.new_contest_form #penalty').val('0');
            $('.new_contest_form #penalty_type').val('none');
            $('.new_contest_form #show_scoreboard_after').val('1');
          });

      // Defaults for CONACUP
      $('#conacup')
          .click(function() {
            $('.new_contest_form #title')
                .attr('placeholder',
                      omegaup.T.contestNewFormTitlePlaceholderConacupStyle);
            $('#window_length_enabled').removeAttr('checked');
            $('#window_length').attr('disabled', 'disabled');
            $('#window_length').val('');

            $('.new_contest_form #scoreboard').val('75');
            $('.new_contest_form #points_decay_factor').val('0');
            $('.new_contest_form #submissions_gap').val('1');
            $('.new_contest_form #feedback').val('yes');
            $('.new_contest_form #penalty').val('20');
            $('.new_contest_form #penalty_type').val('contest_start');
            $('.new_contest_form #show_scoreboard_after').val('1');
          });
    });
