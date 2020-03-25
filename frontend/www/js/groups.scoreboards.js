$(function() {
  $('.navbar #nav-groups').addClass('active');

  var formData = $('#form-data');
  var formName = formData.attr('data-name');
  var formPage = formData.attr('data-page');
  var scoreboardAlias = formData.attr('data-alias');
  var groupAlias = formData.attr('data-group-alias');

  if (formPage === 'edit') {
    omegaup.API.Contest.list()
      .then(function(contests) {
        for (var i = 0; i < contests.results.length; i++) {
          contest = contests.results[i];
          $('#contests').append(
            $('<option></option>')
              .attr('value', contest.alias)
              .text(omegaup.UI.contestTitle(contest)),
          );
        }
      })
      .catch(omegaup.UI.apiError);

    $('#scoreboard-add-contest-form').on('submit', function() {
      omegaup.API.GroupScoreboard.addContest({
        group_alias: groupAlias,
        scoreboard_alias: scoreboardAlias,
        contest_alias: $('#contests').val(),
        only_ac: $('#only-ac').val(),
        weight: $('#weight').val(),
      })
        .then(function(data) {
          omegaup.UI.success(omegaup.T.groupEditScoreboardsContestsAdded);
          refreshScoreboardContests();
        })
        .catch(omegaup.UI.apiError);

      return false;
    });

    refreshScoreboardContests();

    function refreshScoreboardContests() {
      omegaup.API.GroupScoreboard.details({
        group_alias: groupAlias,
        scoreboard_alias: scoreboardAlias,
      })
        .then(function(gScoreboard) {
          $('#scoreboard-contests').empty();

          for (var i = 0; i < gScoreboard.contests.length; i++) {
            var contest = gScoreboard.contests[i];
            $('#scoreboard-contests').append(
              $('<tr></tr>')
                .append(
                  $('<td></td>').append(
                    $('<a></a>')
                      .attr('href', '/arena/' + contest.alias + '/')
                      .text(
                        omegaup.UI.escape(omegaup.UI.contestTitle(contest)),
                      ),
                  ),
                )
                .append(
                  $('<td></td>').append(
                    contest.only_ac ? omegaup.T.wordsYes : omegaup.T.wordsNo,
                  ),
                )
                .append($('<td></td>').append(contest.weight))
                .append(
                  $(
                    '<td><button type="button" class="close"><span class="glyphicon' +
                      ' glyphicon-remove"></span></button></td>',
                  ).on(
                    'click',
                    (function(contestAlias) {
                      return function(e) {
                        omegaup.API.GroupScoreboard.removeContest({
                          group_alias: groupAlias,
                          scoreboard_alias: scoreboardAlias,
                          contest_alias: contestAlias,
                        })
                          .then(function(response) {
                            omegaup.UI.success(
                              omegaup.T.groupEditScoreboardsContestsRemoved,
                            );

                            var tr = e.target.parentElement.parentElement;
                            $(tr).remove();
                          })
                          .catch(omegaup.UI.apiError);
                      };
                    })(contest.alias),
                  ),
                ),
            );
          }
        })
        .catch(omegaup.UI.apiError);
    }
  } else if (formPage === 'details') {
    omegaup.API.GroupScoreboard.details({
      group_alias: groupAlias,
      scoreboard_alias: scoreboardAlias,
    })
      .then(function(scoreboard) {
        var ranking = scoreboard['ranking'];
        $('#scoreboard-title').html(scoreboard.scoreboard.name);

        // Adding contest's column
        for (var c = 0; c < scoreboard.contests.length; c++) {
          var alias = scoreboard.contests[c].alias;

          $(
            '<th><a href="/arena/' +
              alias +
              '" title="' +
              alias +
              '">' +
              c +
              '</a></th>',
          ).insertBefore('#ranking-table thead th.total');

          $('<td class="prob_' + alias + '_points"></td>').insertBefore(
            '#ranking-table tbody.user-list-template td.points',
          );

          $('#ranking-table thead th').attr('colspan', '');
          $('#ranking-table tbody.user-list-template .penalty').remove();
        }

        // Adding scoreboard data:
        // Cleaning up table
        $('#ranking-table tbody.inserted').remove();

        // For each user
        for (var i = 0; i < ranking.length; i++) {
          var rank = ranking[i];

          var r = $('#ranking-table tbody.user-list-template')
            .clone()
            .removeClass('user-list-template')
            .addClass('inserted')
            .addClass('rank-new');

          var username =
            rank.username +
            (rank.name == rank.username
              ? ''
              : ' (' + omegaup.UI.escape(rank.name) + ')');
          $('.user', r).html(username);

          // For each contest in the scoreboard
          for (var c = 0; c < scoreboard.contests.length; c++) {
            var alias = scoreboard.contests[c].alias;
            var contestResults = rank.contests[alias];

            var pointsCell = $('.prob_' + alias + '_points', r);
            pointsCell.html(
              '<div class="points">' +
                (contestResults.points ? '+' + contestResults.points : '0') +
                '</div>\n' +
                '<div class="penalty">' +
                contestResults.penalty +
                '</div>',
            );

            pointsCell.removeClass('pending accepted wrong');
          }

          $('td.points', r).html(
            '<div class="points">' +
              rank.total.points +
              '</div>' +
              '<div class="penalty">' +
              rank.total.penalty +
              '</div>',
          );
          $('.position', r)
            .html(i + 1)
            .removeClass('recent-event');

          $('#ranking-table').append(r);
        }

        $('#ranking').show();
        $('#root').fadeIn('slow');
        $('#loading').fadeOut('slow');
      })
      .catch(omegaup.UI.apiError);
  }
});
