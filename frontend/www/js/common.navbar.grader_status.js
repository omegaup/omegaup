(function() {
  function updateGraderStatus() {
    $('.grader-count').removeClass(
      'grader-error grader-ok grader-warning grader-unknown',
    );
    $('.grader-count').text('…');
    omegaup.API.Grader.status()
      .then(function(stats) {
        var graderInfo = stats.grader;
        var queueLength = -1;
        if (graderInfo.status == 'ok') {
          if (graderInfo.queue) {
            queueLength =
              graderInfo.queue.run_queue_length +
              graderInfo.queue.running.length;
          }
          if (queueLength < 5) {
            $('.grader-count').addClass('grader-ok');
          } else {
            $('.grader-count').addClass('grader-warning');
          }
          $('.grader-status').text('Grader OK');
          $('.grader-broadcaster-sockets').text(
            'Broadcaster sockets: ' + graderInfo.broadcaster_sockets,
          );
          $('.grader-embedded-runner').text(
            'Embedded runner: ' + graderInfo.embedded_runner,
          );
          $('.grader-queues').html(
            'Queues: <pre style="width: 50em;">' +
              omegaup.UI.prettyPrintJSON(graderInfo.queue) +
              '</pre>',
          );
        } else {
          $('.grader-count').addClass('grader-error');
          $('.grader-status').text('Grader DOWN');
        }
        $('.grader-count').text(queueLength);
      })
      .fail(function(stats) {
        $('.grader-count').addClass('grader-error');
        $('.grader-status').text('Grader DOWN');
        $('.grader-broadcaster-sockets').text(
          'API api/grader/status call failed: ' +
            omegaup.UI.escape(stats.error),
        );
        $('.grader-count').text('?');
      });
  }

  updateGraderStatus();
  setInterval(updateGraderStatus, 30000);
})();
