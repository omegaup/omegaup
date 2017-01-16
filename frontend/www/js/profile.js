var username = $('#username').attr('data-username');

omegaup.API.getUserStats(username, function(data) {
  window.run_counts_chart = oGraph.verdictCounts(
    'verdict-chart',
    username,
    data
  );
});

omegaup.API.getContestStatsForUser(username, function(data) {
  $('#contest-results-wait').hide();
  t = 0;
  for (var contest_alias in data['contests']) {
    var now = omegaup.OmegaUp.time();
    var end = omegaup.OmegaUp.time(
      data['contests'][contest_alias]['data']['finish_time'] * 1000
    );

    if (data['contests'][contest_alias]['place'] != null && now > end) {
      var title = data['contests'][contest_alias]['data']['title'];
      var place = data['contests'][contest_alias]['place'];
      var content = "<tr><td><a href='/arena/" +
        contest_alias +
        "'>" +
        title +
        '</a></td><td><b>' +
        place +
        '</b></td></tr>';
      $('#contest-results tbody').append(content);
      t++;
    }
  }

  $('#contests-total').html(t);
});

omegaup.API.getProblemsSolved(username, function(data) {
  $('#problems-solved-wait').hide();

  for (var i = 0; i < data['problems'].length; i++) {
    var content = '<tr>';

    for (var j = 0; j < 3 && i < data['problems'].length; j++, i++) {
      content += "<td><a href='/arena/problem/" +
        data['problems'][i]['alias'] +
        "'>" +
        data['problems'][i]['title'] +
        '</a></td>';
    }
    i--;

    content += '</tr>';

    $('#problems-solved tbody').append(content);
  }

  $('#problems-solved-total').html(data['problems'].length);
});
