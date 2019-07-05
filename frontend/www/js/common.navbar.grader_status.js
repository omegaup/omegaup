(function() {
  var graderCount = $('.grader-count');
  var graderDropDown = $('.grader-status');

  function updateGraderStatus() {
    graderCount.removeClass(
        'grader-error grader-ok grader-warning grader-unknown');
    graderCount.html("<img src='/media/waitcircle.gif' />");
    var html = '<a class="grader-status-link" href="/arena/admin/">' +
               omegaup.T.wordsLatestSubmissions + '</a>';
    omegaup.API.Grader.status()
        .then(function(stats) {
          var graderInfo = stats.grader;
          var queueLength = -1;
          if (graderInfo.status == 'ok') {
            if (graderInfo.queue) {
              queueLength = graderInfo.queue.run_queue_length +
                            graderInfo.queue.running.length;
            }
            if (queueLength < 5) {
              graderCount.addClass('grader-ok');
            } else {
              graderCount.addClass('grader-warning');
            }
            html += '<p>Grader OK</p>';
            html += '<p>Broadcaster sockets: ' +
                    graderInfo.broadcaster_sockets + '</p>';
            html += '<p>Embedded runner: ' + graderInfo.embedded_runner +
                    '</p>';
            html += '<p>Queues: <pre style="width: 50em;">' +
                    omegaup.UI.prettyPrintJSON(graderInfo.queue) +
                    '</pre></p>';
          } else {
            graderCount.addClass('grader-error');
            html += '<p>Grader DOWN</p>';
          }

          graderCount.text(queueLength);
          graderDropDown.html(html);
        })
        .fail(function(stats) {
          graderCount.addClass('grader-error');
          html += '<p>Grader DOWN</p>';
          html += '<p>API api/grader/status call failed:';
          html += omegaup.UI.escape(stats.error);
          html += '</p>';
          graderCount.text('?');
          graderDropDown.html(html);
        });
  }

  updateGraderStatus();
  setInterval(updateGraderStatus, 30000);
})();
