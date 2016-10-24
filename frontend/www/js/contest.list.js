(function() {
  function makeWorldClockLink(date) {
    try {
      return 'http://timeanddate.com/worldclock/fixedtime.html?iso=' +
             date.toISOString();
    } catch (e) {
      return '#';
    }
  }

  function fillContestsTable() {
    omegaup.API.getMyContests(function(contests) {
      // Got the contests, lets draw them

      var html = '';

      for (var i = 0; i < contests.results.length; i++) {
        var startDate = contests.results[i].start_time;
        var endDate = contests.results[i].finish_time;
        html += '<tr>' + "<td><input type='checkbox' id='" +
                contests.results[i].alias + "'/></td>" +
                "<td><b><a href='/arena/" + contests.results[i].alias + "/'>" +
                omegaup.UI.escape(contests.results[i].title) + '</a></b></td>' +
                '<td><a href="' + makeWorldClockLink(startDate) + '">' +
                startDate.format('long', 'es') + '</a></td>' +
                '<td><a href="' + makeWorldClockLink(endDate) + '">' +
                endDate.format('long', 'es') + '</a></td>' +
                '<td>' +
                ((contests.results[i].public == '1') ? omegaup.T['wordsYes'] :
                                                       omegaup.T['wordsNo']) +
                '</td>' +
                '<td>' +
                ((contests.results[i].scoreboard_url == null) ?
                     '' :
                     '<a class="glyphicon glyphicon-link" href="/arena/' +
                         contests.results[i].alias + '/scoreboard/' +
                         contests.results[i].scoreboard_url + '" title="' +
                         omegaup.T['contestScoreboardLink'] +
                         '"> Public</a></td>') +
                '<td>' +
                ((contests.results[i].scoreboard_url_admin == null) ?
                     '' :
                     '<a class="glyphicon glyphicon-link" href="/arena/' +
                         contests.results[i].alias + '/scoreboard/' +
                         contests.results[i].scoreboard_url_admin +
                         '" title="' + omegaup.T['contestScoreboardAdminLink'] +
                         '"> Admin</a></td>') +
                '<td><a class="glyphicon glyphicon-edit" href="/contest/' +
                contests.results[i].alias + '/edit/" title="' +
                omegaup.T['wordsEdit'] + '"></a></td>' +
                '<td><a class="glyphicon glyphicon-dashboard" href="/arena/' +
                contests.results[i].alias + '/admin/" title="' +
                omegaup.T['contestListSubmissions'] + '"></a></td>' +
                '<td><a class="glyphicon glyphicon-stats" href="/contest/' +
                contests.results[i].alias + '/stats/" title="' +
                omegaup.T['profileStatistics'] + '"></a></td>' +
                '<td><a class="glyphicon glyphicon-print" href="/arena/' +
                contests.results[i].alias + '/print/" title="' +
                omegaup.T['contestPrintableVersion'] + '"></a></td>' +
                '</tr>';
      }

      $('#contest_list').removeClass('wait_for_ajax');
      $('#contest_list > table > tbody').empty().html(html);
    });
  }
  fillContestsTable();

  $('#bulk-make-public')
      .click(function() {
        omegaup.UI.bulkOperation(
            function(alias, handleResponseCallback) {
              omegaup.API.updateContest(alias, null, null, null, null, null,
                                        null, null, null, null, null,
                                        1 /*public*/, null, null, null, null,
                                        handleResponseCallback);
            },
            function() { fillContestsTable(); });
      });

  $('#bulk-make-private')
      .click(function() {
        omegaup.UI.bulkOperation(
            function(alias, handleResponseCallback) {
              omegaup.API.updateContest(alias, null, null, null, null, null,
                                        null, null, null, null, null,
                                        0 /*public*/, null, null, null, null,
                                        handleResponseCallback);
            },
            function() { fillContestsTable(); });
      });
})();
