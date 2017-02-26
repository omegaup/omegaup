omegaup.OmegaUp.on('ready', function() {
  $('form#new_interview_form')
      .submit(function(ev) {
        ev.preventDefault();
        omegaup.API.Interview.create({
                               alias: $('#alias').val(),
                               title: $('#title').val(),
                               duration: $('#duration').val()
                             })
            .then(function(response) {
              omegaup.UI.success(omegaup.T['interviewCreatedSuccess']);
              fillInterviewsTable();
            })
            .fail(omegaup.UI.apiError);
      });

  function fillInterviewsTable() {
    omegaup.API.Inteview.list()
        .then(function(interviews) {
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
        })
        .fail(omegaup.UI.apiError);
  }

  fillInterviewsTable();
});
