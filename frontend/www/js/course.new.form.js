$('document')
    .ready(function() {
      $('#start_time, #finish_time')
          .datepicker({
            weekStart: 1,
            format: 'mm/dd/yyyy',
            startDate: Date.create(Date.now()),
          });

      if ($('#start_time').val() == '') {
        // Defaults for start_time and end_time
        var defaultDate = Date.create(Date.now());
        defaultDate.set({seconds: 0});
        $('#start_time').val(omegaup.UI.formatDate(defaultDate));
        defaultDate.setHours(defaultDate.getDate() + 30);
        $('#finish_time').val(omegaup.UI.formatDate(defaultDate));
      }
    });
