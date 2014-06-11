<div class="wait_for_ajax panel panel-default" id="problems_list" >
	<div class="panel-heading">
		<h3 class="panel-title">{#wordProblems#}</h3>
	</div>
	<table class="table">
		<thead>
			<tr>
				<th>{#wordsTitle#}</th>
				<th>{#wordsRuns#}</th>
				<th>{#wordsSolved#}</th>
				<th>{#wordsRatio#}</th>
				<th>{#wordsPointsForRank#}</th>
				<th>{#wordsMyScore#}</th>
			</tr>
		</thead>
		<tbody></tbody>
	</table>
</div>
<script>
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
</script>

