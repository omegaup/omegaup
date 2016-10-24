(function() {
  var problemsSolved = $('#rank-by-problems-solved');
  var length = parseInt(problemsSolved.attr('data-length'));
  var page = parseInt(problemsSolved.attr('data-page'));
  var is_index = (problemsSolved.attr('is-index') === '1' ? true : false);
  omegaup.API.getRankByProblemsSolved(page, length, function(result) {
    var html = '';
    for (a = 0; a < result.rank.length; a++) {
      html += '<tr>' +
              '<td>' + result.rank[a].rank + '</td>' +
              '<td class=\"flagColumn\">' +
              omegaup.UI.getFlag(result.rank[a]['country_id']) + '</td>' +
              '<td class=\"forcebreaks forcebreaks-top-5\">' +
              '<b><a href=/profile/' + result.rank[a].username + '>' +
              result.rank[a].username + '</a></b>' +
              '<br/>' +
              (result.rank[a].name == null ? '&nbsp;' : result.rank[a].name) +
              '</td>' +
              '<td class=\"numericColumn\">' + result.rank[a].score + '</td>' +
              (is_index ? '' : ("<td class='numericColumn'>" +
                                result.rank[a].problems_solved + '</td>')) +
              '</tr>';
    }
    $('#rank-by-problems-solved>tbody').append(html);
  });
})();
