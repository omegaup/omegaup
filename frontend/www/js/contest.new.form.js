omegaup.OmegaUp.on('ready', function() {
  $('#start-time, #finish-time')
      .datetimepicker({
        weekStart: 1,
        format: 'mm/dd/yyyy hh:ii',
        startDate: Date.create(Date.now()),
      });

  if ($('#start-time').val() == '') {
    // Defaults for start-time and end_time
    var defaultDate = Date.create(Date.now());
    defaultDate.set({seconds: 0});
    $('#start-time').val(omegaup.UI.formatDateTime(defaultDate));
    defaultDate.setHours(defaultDate.getHours() + 5);
    $('#finish-time').val(omegaup.UI.formatDateTime(defaultDate));
  }

  $('#window-length-enabled')
      .change(function() {
        if ($(this).is(':checked')) {
          $('#window-length').removeAttr('disabled');
        } else {
          $('#window-length').attr('disabled', 'disabled');
        }
      });

  // Defaults for OMI
  $('#omi')
      .click(function() {
        $('.new_contest_form #title')
            .attr('placeholder',
                  omegaup.T.contestNewFormTitlePlaceholderOmiStyle);
        $('#window-length-enabled').removeAttr('checked');
        $('#window-length').attr('disabled', 'disabled');
        $('#window-length').val('');

        $('.new_contest_form #scoreboard').val('0');
        $('.new_contest_form #points-decay-factor').val('0');
        $('.new_contest_form #submissions-gap').val('1');
        $('.new_contest_form #feedback').val('yes');
        $('.new_contest_form #penalty').val('0');
        $('.new_contest_form #penalty-type').val('none');
        $('.new_contest_form #show-scoreboard-after').val('1');
      });

  // Defaults for preselectivos IOI
  $('#preioi')
      .click(function() {
        $('.new_contest_form #title')
            .attr('placeholder',
                  omegaup.T.contestNewFormTitlePlaceholderIoiStyle);
        $('#window-length-enabled').attr('checked', 'checked');
        $('#window-length').removeAttr('disabled');
        $('#window-length').val('180');

        $('.new_contest_form #scoreboard').val('0');
        $('.new_contest_form #points-decay-factor').val('0');
        $('.new_contest_form #submissions-gap').val('0');
        $('.new_contest_form #feedback').val('yes');
        $('.new_contest_form #penalty').val('0');
        $('.new_contest_form #penalty-type').val('none');
        $('.new_contest_form #show-scoreboard-after').val('1');
      });

  // Defaults for CONACUP
  $('#conacup')
      .click(function() {
        $('.new_contest_form #title')
            .attr('placeholder',
                  omegaup.T.contestNewFormTitlePlaceholderConacupStyle);
        $('#window-length-enabled').removeAttr('checked');
        $('#window-length').attr('disabled', 'disabled');
        $('#window-length').val('');

        $('.new_contest_form #scoreboard').val('75');
        $('.new_contest_form #points-decay-factor').val('0');
        $('.new_contest_form #submissions-gap').val('1');
        $('.new_contest_form #feedback').val('yes');
        $('.new_contest_form #penalty').val('20');
        $('.new_contest_form #penalty-type').val('contest_start');
        $('.new_contest_form #show-scoreboard-after').val('1');
      });
});
