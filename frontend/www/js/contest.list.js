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
    var deferred = $('#show-admin-contests').prop('checked') ?
                       omegaup.API.getAdminContests() :
                       omegaup.API.getMyContests();
    deferred.then(function(result) {
      // Got the contests, lets draw them

      var html = '';

      for (var i = 0; i < result.contests.length; i++) {
        var contest = result.contests[i];
        var startDate = contest.start_time;
        var endDate = contest.finish_time;
        html +=
            '<tr>' + "<td><input type='checkbox' id='" + contest.alias +
            "'/></td>" + "<td><b><a href='/arena/" + contest.alias + "/'>" +
            omegaup.UI.escape(contest.title) + '</a></b></td>' +
            '<td><a href="' + makeWorldClockLink(startDate) + '">' +
            startDate.format('long', 'es') + '</a></td>' +
            '<td><a href="' + makeWorldClockLink(endDate) + '">' +
            endDate.format('long', 'es') + '</a></td>' +
            '<td>' + ((contest.public == '1') ? omegaup.T['wordsYes'] :
                                                omegaup.T['wordsNo']) +
            '</td>' +
            '<td>' +
            ((contest.scoreboard_url == null) ?
                 '' :
                 '<a class="glyphicon glyphicon-link" href="/arena/' +
                     contest.alias + '/scoreboard/' + contest.scoreboard_url +
                     '" title="' + omegaup.T['contestScoreboardLink'] +
                     '"> Public</a></td>') +
            '<td>' + ((contest.scoreboard_url_admin == null) ?
                          '' :
                          '<a class="glyphicon glyphicon-link" href="/arena/' +
                              contest.alias + '/scoreboard/' +
                              contest.scoreboard_url_admin + '" title="' +
                              omegaup.T['contestScoreboardAdminLink'] +
                              '"> Admin</a></td>') +
            '<td><a class="glyphicon glyphicon-edit" href="/contest/' +
            contest.alias + '/edit/" title="' + omegaup.T['wordsEdit'] +
            '"></a></td>' +
            '<td><a class="glyphicon glyphicon-dashboard" href="/arena/' +
            contest.alias + '/admin/" title="' +
            omegaup.T['contestListSubmissions'] + '"></a></td>' +
            '<td><a class="glyphicon glyphicon-stats" href="/contest/' +
            contest.alias + '/stats/" title="' +
            omegaup.T['profileStatistics'] + '"></a></td>' +
            '<td><a class="glyphicon glyphicon-time" href="/contest/' +
            contest.alias + '/activity/" title="' +
            omegaup.T['contestActivityReport'] + '"></a></td>' +
            '<td><a class="glyphicon glyphicon-print" href="/arena/' +
            contest.alias + '/print/" title="' +
            omegaup.T['contestPrintableVersion'] + '"></a></td>' +
            '</tr>';
      }

      $('#contest_list').removeClass('wait_for_ajax');
      $('#contest_list > table > tbody').empty().html(html);
    });
  }
  fillContestsTable();

  $('#show-admin-contests').click(fillContestsTable);

  $('#bulk-make-public')
      .click(function() {
        omegaup.UI.bulkOperation(
            function(alias, handleResponseCallback) {
              omegaup.API.updateContest({contest_alias: alias, public: 1})
                  .then(handleResponseCallback);
            },
            function() { fillContestsTable(); });
      });

  $('#bulk-make-private')
      .click(function() {
        omegaup.UI.bulkOperation(
            function(alias, handleResponseCallback) {
              omegaup.API.updateContest({contest_alias: alias, public: 0})
                  .then(handleResponseCallback);
            },
            function() { fillContestsTable(); });
      });
})();
