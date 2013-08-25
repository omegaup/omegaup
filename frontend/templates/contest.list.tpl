<div class="wait_for_ajax panel panel-default" id="contest_list">
	<div class="panel-heading">
		<h3 class="panel-title">Concursos</h3>
	</div>
	
	<table class="table">
		<thead>
			<th>Título</th>
			<th>Inicio</th>
			<th>Fin</th>
			<th>Público</th>
			<th></th>
			<th></th>
			<th></th>
			<th></th>
			<th></th>
			<th></th>
		</thead>
		<tbody>
		<tbody>
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
	
		omegaup.getMyContests(function(contests) {
			// Got the contests, lets draw them

			var html = "";

			for (var i = 0; i < contests.results.length; i++) {
				var startDate = contests.results[i].start_time;
				var endDate = contests.results[i].finish_time;
				html += "<tr>"
					+ "<td><b><a href='/arena/" + contests.results[i].alias  + "/'>" + contests.results[i].title + "</a></b></td>"					
					+ '<td><a href="' + makeWorldClockLink(startDate) + '">' + startDate.format("long", "es") + "</a></td>"
					+ '<td><a href="' + makeWorldClockLink(endDate) + '">' + endDate.format("long", "es") + "</a></td>"
					+ '<td>'+ ((contests.results[i].public == '1') ? 'Sí' : 'No')  + '</td>'
					+ '<td><a href="/contestedit.php?contest=' + contests.results[i].alias  + '">Editar</a></td>'
					+ '<td><a href="/addproblemtocontest.php?contest=' + contests.results[i].alias  + '">Agregar problemas</a></td>'
					+ '<td><a href="/addusertoprivatecontest.php?contest=' + contests.results[i].alias  + '">Agregar concursantes</a></td>'
					+ "<td><a href='/arena/" + contests.results[i].alias  + "/admin/'>Envíos</a></td>"
					+ '<td><a href="/conteststats.php?contest=' + contests.results[i].alias  + '">Estadísticas</a></td>'
					+ "</tr>";
			}

			$("#contest_list").removeClass("wait_for_ajax");
			$("#contest_list > table > tbody").empty().append(html);
		});
	})();
</script>

