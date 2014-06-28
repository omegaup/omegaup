(function(){
	function makeWorldClockLink(date) {
		try {
			return "http://timeanddate.com/worldclock/fixedtime.html?iso=" + date.toISOString();
		} catch (e) {
			return '#';
		}
	}

	omegaup.getProblems(function(problems) {
		// Got the problems, lets draw them

		var html = "";

		if (document.location.search.indexOf('sort=difficulty') != -1) {
			problems.results.sort(function (a, b) {
				if (a.accepted != b.accepted)
					return a.accepted - b.accepted;
				return b.submissions - a.submissions;
			});
		}

		for (var i = 0; i < problems.results.length; i++) {
			var accepted = problems.results[i].accepted;
			var submissions = problems.results[i].submissions;
			var ratio = (problems.results[i].submissions > 0) ? ((accepted/(submissions*1.0))*100).toFixed(2) : 0.0;				
			html += "<tr>"
				+ '<td><a href="/arena/problem/' + problems.results[i].alias  + '">' + omegaup.escape(problems.results[i].title) + "</a></td>"
				+ "<td>" + submissions + "</td>"
				+ "<td>" + accepted  + "</td>"
				+ "<td>" + ratio + "%</td>"
				+ "<td>" + problems.results[i].rankPoints + "</td>"
				+ "<td><b>" + problems.results[i].score + "</b></td>"
				+ "</tr>";
		}

		$("#problems_list").removeClass("wait_for_ajax");
		$("#problems_list tbody").append(html);
	});
})();
	
// Enable tooltip
$(function () {
	$("[rel='tooltip']").tooltip();
});

