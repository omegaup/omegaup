<div class="post">
	<div class="copy wait_for_ajax" id="contest_list" >
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
	
		omegaup.getContests(function(contests) {
			// Got the contests, lets draw them

			var html = "<h3>Concursos</h3><table><tr>"
					+ "<td>Title</td>"
					+ "<td>Descripcion</td>"
					+ "<td>Inicio</td>"
					+ "<td>Fin</td>"
					+ "<td></td>"
					+ "</tr>";

			for (var i = 0; i < contests.results.length; i++) {
				var startDate = contests.results[i].start_time;
				var endDate = contests.results[i].finish_time;
				html += "<tr>"
					+ "<td>" + contests.results[i].title + "</td>"
					+ "<td>" + contests.results[i].description + "</td>"
					+ '<td><a href="' + makeWorldClockLink(startDate) + '">' + startDate.format("long", "es") + "</a></td>"
					+ '<td><a href="' + makeWorldClockLink(endDate) + '">' + endDate.format("long", "es") + "</a></td>"
					+ '<td><a href="/contest/' + contests.results[i].alias  + '">Detalles</a></td>'
					+ '<td><a href="/arena/' + contests.results[i].alias  + '">Ir al concurso</a></td>'
					+ "</tr>";
			}

			html += "</table>";

			$("#contest_list").removeClass("wait_for_ajax").append(html);
		});
	})();
</script>

