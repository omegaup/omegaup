omegaup.OmegaUp.on('ready', function() {
  var assignmentForm = $('.new_course_assignment_form');
  assignmentForm
      .submit(function() {
        var courseAlias = /\/course\/([^\/]+)\/edit\/?.*/.exec(
            window.location.pathname)[1];

        omegaup.API.createCourseAssignment(
            courseAlias, $('#title', assignmentForm).val(),
            $('#description', assignmentForm).val(),
            (new Date($('#start_time', assignmentForm).val())
                  .getTime()) /
                1000,
            (new Date($('#finish_time', assignmentForm).val())
                  .getTime()) /
                1000,
            $('#alias', assignmentForm).val(),
            $('#assignment_type', assignmentForm).val(),
            function(data) {
              if (data.status == 'ok') {
                omegaup.UI.success(omegaup.T['courseAssignmentAdded']);
                assignmentForm[0].reset();
                setDefaultDates();
              } else {
                omegaup.UI.error(data.error || 'error');
              }
            });

        return false;
      });

  $('#start_time, #finish_time', assignmentForm)
      .datetimepicker({
        weekStart: 1,
        format: 'mm/dd/yyyy hh:ii',
        startDate: Date.create(Date.now()),
      });

  function setDefaultDates() {
    // Defaults for start_time and end_time
    var defaultDate = Date.create(Date.now());
    defaultDate.set({seconds: 0});
    $('#start_time', assignmentForm)
        .val(omegaup.UI.formatDateTime(defaultDate));
    defaultDate.setHours(defaultDate.getHours() + 5);
    $('#finish_time', assignmentForm)
        .val(omegaup.UI.formatDateTime(defaultDate));
  }
  if ($('#start_time', assignmentForm).val() == '') {
    setDefaultDates();
  }
});
