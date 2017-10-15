(function() {
  var graderStatus = $('#grader-status > a');
  var graderCount = $('#grader-count');
  var graderDropDown = $('#grader-status .dropdown-menu');

  function updateGraderStatus() {
    graderStatus.removeClass(
        'grader-error grader-ok grader-warning grader-unknown');
    graderCount.html("<img src='/media/waitcircle.gif' />");
    var html = '<li><a href="/arena/admin/">' +
               omegaup.T.wordsLatestSubmissions + '</a></li>';
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
              graderStatus.addClass('grader-ok');
            } else {
              graderStatus.addClass('grader-warning');
            }
            html += '<li><a>Grader OK</a></li>';
            html += '<li><a>Broadcaster sockets: ' +
                    graderInfo.broadcaster_sockets + '</a></li>';
            html += '<li><a>Embedded runner: ' + graderInfo.embedded_runner +
                    '</a></li>';
            html += '<li><a>Queues: <pre style="width: 50em;">' +
                    omegaup.UI.prettyPrintJSON(graderInfo.queue) +
                    '</pre></a></li>';
          } else {
            graderStatus.addClass('grader-error');
            html += '<li><a>Grader DOWN</a></li>';
          }

          graderCount.text(queueLength);
          graderDropDown.html(html);
        })
        .fail(function(stats) {
          graderStatus.addClass('grader-unknown');
          html += '<li><a>Grader DOWN</a></li>';
          html += '<li><a>API api/grader/status call failed:';
          html += omegaup.UI.escape(stats.error);
          html += '</a></li>';
          graderCount.text('?');
          graderDropDown.html(html);
        });
  }

  updateGraderStatus();
  setInterval(updateGraderStatus, 30000);
})();
