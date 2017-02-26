omegaup.OmegaUp.on('ready', function() {
  var formSubmit = function() {
    omegaup.API.createInterview(
        $('#alias').val(), $('#title').val(), $('#duration').val(),
        function(response) {
          if (response.status == 'ok') {
            omegaup.UI.success(omegaup.T.interviewCreatedSuccess);
            fillInterviewsTable();
          } else {
            omegaup.UI.error(response.error);
          }
        });

    return false;  // Prevent page refresh on submit
  };

  $('form#new_interview_form').submit(formSubmit);

  function fillInterviewsTable() {
    omegaup.API.getInterviews(function(interviews) {
      var html = '';
      for (var i = interviews.results.length - 1; i >= 0; i--) {
        html += '<tr>' +
                '<td></td>' + "<td><b><a href='/interview/" +
                interviews.results[i].alias + "/edit'>" +
                omegaup.UI.escape(interviews.results[i].title) +
                '</a></b></td>' +
                '<td>' + interviews.results[i].window_length + '</td>' +
                '<td></td>' +
                '</tr>';
      }

      $('#contest_list').removeClass('wait_for_ajax');
      $('#contest_list > table > tbody').empty().html(html);
    });
  }

  fillInterviewsTable();
});
