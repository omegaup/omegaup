(function() {
	var problemsSolved = $('#rank-by-problems-solved');
	var length = parseInt(problemsSolved.attr('data-length'));
	var page = parseInt(problemsSolved.attr('data-page'));
	omegaup.getRankByProblemsSolved(
			length * page,
			length,
			function(result) {
				var html = "";
				for (a = 0; a < result.rank.length; a++) {
					html += "<tr><td>" + result.rank[a].rank + "</td><td><b><a href=/profile/"+ result.rank[a].username + ">"
					+ ""+result.rank[a].username + "</a></b>"
					+ "<br/>" + (result.rank[a].name == null ? "&nbsp;" : result.rank[a].name) + "</td>"
					+ "<td>"+result.rank[a].problems_solved + "</td></tr>";
				}
				$("#rank-by-problems-solved>tbody").append(html);
		}
	);
})();
