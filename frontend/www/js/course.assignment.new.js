omegaup.OmegaUp.on('ready', function() {
  var assignmentForm = $('.new_course_assignment_form');
  var startTime = $('input[name="start_time"]', assignmentForm);
  var finishTime = $('input[name="finish_time"]', assignmentForm);
  assignmentForm
      .submit(function() {
        var courseAlias = /\/course\/([^\/]+)\/edit\/?.*/.exec(
            window.location.pathname)[1];

        omegaup.API.createCourseAssignment(
            courseAlias, $('input[name="title"]', assignmentForm).val(),
            $('textarea[name="description"]', assignmentForm).val(),
            (new Date(startTime.val()).getTime()) / 1000,
            (new Date(finishTime.val()).getTime()) / 1000,
            $('input[name="alias"]', assignmentForm).val(),
            $('select[name="assignment_type"]', assignmentForm).val(),
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

  $([startTime[0], finishTime[0]])
      .datetimepicker({
        weekStart: 1,
        format: 'mm/dd/yyyy hh:ii',
        startDate: Date.create(Date.now()),
      });

  function setDefaultDates() {
    // Defaults for start_time and end_time
    var defaultDate = Date.create(Date.now());
    defaultDate.set({seconds: 0});
    startTime.val(omegaup.UI.formatDateTime(defaultDate));
    defaultDate.setHours(defaultDate.getHours() + 5);
    finishTime.val(omegaup.UI.formatDateTime(defaultDate));
  }
  if (startTime.val() == '') {
    setDefaultDates();
  }
});
