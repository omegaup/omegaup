omegaup.OmegaUp.on('ready', function() {
  var courseForm = $('.new_course_form');
  $('#start_time, #finish_time', courseForm)
      .datepicker({
        weekStart: 1,
        format: 'mm/dd/yyyy',
        startDate: Date.create(Date.now()),
      });

  if ($('#start_time', courseForm).val() == '') {
    // Defaults for start_time and end_time
    var defaultDate = Date.create(Date.now());
    defaultDate.set({seconds: 0});
    $('#start_time', courseForm).val(omegaup.UI.formatDate(defaultDate));
    defaultDate.setDate(defaultDate.getDate() + 30);
    $('#finish_time', courseForm).val(omegaup.UI.formatDate(defaultDate));
  }
});
