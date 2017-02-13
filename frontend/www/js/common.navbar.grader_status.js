(function() {
  function updateGraderStatus() {
    $('#grader-status > a').removeClass(
      'grader-error grader-ok grader-warning grader-unknown'
    );
    $('#grader-count').html("<img src='/media/waitcircle.gif' />");
    var html = "<li><a href='/arena/admin/'>Últimos envíos</a></li>";
    omegaup.API.getGraderStats(function(stats) {
      if (stats && stats.status == 'ok') {
        var graderInfo = stats.grader;
        var queueLength = -1;

        if (graderInfo.status == 'ok') {
          var now = omegaup.OmegaUp.time().getTime() / 1000;
          if (graderInfo.queue) {
            queueLength = graderInfo.queue.run_queue_length +
              graderInfo.queue.running.length;
          }
          if (queueLength < 5) {
            $('#grader-status > a').addClass('grader-ok');
          } else {
            $('#grader-status > a').addClass('grader-warning');
          }
          html += '<li><a href="#">Grader OK</a></li>';
          html += '<li><a href="#">Broadcaster sockets: ' +
            graderInfo.broadcaster_sockets +
            '</a></li>';
          html += '<li><a href="#">Embedded runner: ' +
            graderInfo.embedded_runner +
            '</a></li>';
          html += '<li><a href="#">Queues: <pre style="width: 50em;">' +
            omegaup.UI.prettyPrintJSON(graderInfo.queue) +
            '</pre></a></li>';
        } else {
          $('#grader-status > a').addClass('grader-error');
          html += '<li><a href="#">Grader DOWN</a></li>';
        }

        $('#grader-count').html(queueLength);
      } else {
        $('#grader-status > a').addClass('grader-unknown');
        html += '<li><a href="#">Grader DOWN</a></li>';
        html += '<li><a href="#">API api/grader/status call failed:';
        html += stats.error
          .replace(/&/g, '&amp;')
          .replace(/</g, '&lt;')
          .replace(/>/g, '&gt;');
        html += '</a></li>';
        $('#grader-count').html('?');
      }
      $('#grader-status .dropdown-menu').html(html);
    });
  }

  updateGraderStatus();
  setInterval(updateGraderStatus, 30000);
})();
