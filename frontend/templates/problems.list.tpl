<div class="post">
	<div class="copy wait_for_ajax" id="problems_list" >
	</div>
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

			var html = "<h3>Problemas</h3><table><tr>"
					+ "<td>Título</td>"	
					+ "<td>Envíos</td>"
					+ "<td>Resueltos</td>"
					+ "</tr>";

			for (var i = 0; i < problems.results.length; i++) {
				html += "<tr>"
					+ '<td><a href="/arena/problem/' + problems.results[i].alias  + '">' + problems.results[i].title + "</a></td>"
					+ "<td>" + problems.results[i].submissions + "</td>"
					+ "<td>" + problems.results[i].accepted + "</td>"
					+ "</tr>";
			}

			html += "</table>";

			$("#problems_list").removeClass("wait_for_ajax").append(html);
		});
	})();
</script>

