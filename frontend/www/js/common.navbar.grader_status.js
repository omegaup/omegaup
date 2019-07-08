(function() {
  var graderCount = $('.grader-count');

  function updateGraderStatus() {
    graderCount.removeClass(
        'grader-error grader-ok grader-warning grader-unknown');
    graderCount.html("<img src='/media/waitcircle.gif' />");
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
            $('.grader-status').text('Grader OK');
            $('.grader-broadcaster-sockets')
                .text('Broadcaster sockets: ' + graderInfo.broadcaster_sockets);
            $('.grader-embedded-runner')
                .text('Embedded runner: ' + graderInfo.embedded_runner);
            $('.grader-queues')
                .html('Queues: <pre style="width: 50em;">' +
                      omegaup.UI.prettyPrintJSON(graderInfo.queue) + '</pre>');
          } else {
            graderCount.addClass('grader-error');
            $('.grader-status').text('Grader DOWN');
          }
          graderCount.text(queueLength);
        })
        .fail(function(stats) {
          graderCount.addClass('grader-error');
          $('.grader-status').text('Grader DOWN');
          $('.grader-broadcaster-sockets')
              .text('API api/grader/status call failed: ' +
                    omegaup.UI.escape(stats.error));
          graderCount.text('?');
        });
  }

  updateGraderStatus();
  setInterval(updateGraderStatus, 30000);
})();
