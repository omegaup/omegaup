var username = $('#username').attr('data-username');

omegaup.API.User.stats({username: username})
    .then(function(data) {
      window.run_counts_chart =
          oGraph.verdictCounts('verdict-chart', username, data);
    })
    .fail(omegaup.UI.apiError);

omegaup.API.User.contestStats({username: username})
    .then(function(data) {
      $('#contest-results-wait').hide();
      t = 0;
      for (var contest_alias in data['contests']) {
        var now = new Date();
        var end = omegaup.OmegaUp.remoteTime(
            data['contests'][contest_alias]['data']['finish_time'] * 1000);

        if (data['contests'][contest_alias]['place'] != null && now > end) {
          var title = data['contests'][contest_alias]['data']['title'];
          var place = data['contests'][contest_alias]['place'];
          var content = "<tr><td><a href='/arena/" + contest_alias + "'>" +
                        title + '</a></td><td><b>' + place + '</b></td></tr>';
          $('#contest-results tbody').append(content);
          t++;
        }
      }

      $('#contests-total').html(t);
    })
    .fail(omegaup.UI.apiError);

omegaup.API.User.problemsSolved({username: username})
    .then(function(data) {
      $('#problems-solved-wait').hide();

      for (var i = 0; i < data['problems'].length; i++) {
        var content = '<tr>';

        for (var j = 0; j < 3 && i < data['problems'].length; j++, i++) {
          content += "<td><a href='/arena/problem/" +
                     data['problems'][i]['alias'] + "'>" +
                     data['problems'][i]['title'] + '</a></td>';
        }
        i--;

        content += '</tr>';

        $('#problems-solved tbody').append(content);
      }

      $('#problems-solved-total').html(data['problems'].length);
    })
    .fail(omegaup.UI.apiError);
