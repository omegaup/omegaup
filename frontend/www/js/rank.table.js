(function() {
  var problemsSolved = $('#rank-by-problems-solved');
  var length = parseInt(problemsSolved.attr('data-length'));
  var page = parseInt(problemsSolved.attr('data-page'));
  var filter = problemsSolved.attr('data-filter');
  var isIndex = (problemsSolved.attr('is-index') === '1');
  var rowTemplate = '<tr>' +
                    '<td>%(rank)</td><td class="flagColumn">%(flag)</td>' +
                    '<td class="forcebreaks forcebreaks-top-5"><strong>' +
                    '<a href="/profile/%(username)">%(username)</a></strong>' +
                    '%(name)</td>' +
                    '<td class="numericColumn">%(score)</td>' +
                    '%(problemsSolvedRow)' +
                    '</tr>';
  omegaup.API.User.rankByProblemsSolved(
                      {offset: page, rowcount: length, filter: filter})
      .then(function(result) {
        var html = '';
        for (var i = 0; i < result.rank.length; ++i) {
          var user = result.rank[i];
          var problemsSolvedRow = '';
          if (!isIndex) {
            problemsSolvedRow =
                "<td class='numericColumn'>" + user.problems_solved + '</td>';
          }
          html += omegaup.UI.formatString(rowTemplate, {
            rank: user.rank,
            flag: omegaup.UI.getFlag(user.country_id),
            username: user.username,
            name: (user.name == null || length == 5 ? '&nbsp;' :
                                                      ('<br/>' + user.name)),
            score: user.score,
            problemsSolvedRow: problemsSolvedRow,
          });
        }
        $('#rank-by-problems-solved>tbody').append(html);
      })
      .fail(omegaup.UI.apiError);

  $('.filter')
      .on('change', function(evt) {
        // change url parameters with jquery
        // https://samaxes.com/2011/09/change-url-parameters-with-jquery/
        var queryParameters = {}, queryString = location.search.substring(1),
            re = /([^&=]+)=([^&]*)/g, m;
        while (m = re.exec(queryString)) {
          queryParameters[decodeURIComponent(m[1])] = decodeURIComponent(m[2]);
        }
        queryParameters['filter'] = $(this).val();
        window.location.search = $.param(queryParameters);
      });
})();
