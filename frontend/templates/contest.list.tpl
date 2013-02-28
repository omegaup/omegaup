
<div class="post">
	<div class="copy wait_for_ajax" id="contest_list" >
	</div>
</div>
<script>
	(function(){
		omegaup.getContests(function(contests){
			// Got the contests, lets draw them

			var html = "<h3>Concursos</h3><table><tr>"
					+ "<td>Title</td>"
					+ "<td>Descripcion</td>"
					+ "<td>Inicio</td>"
					+ "<td>fin</td>"
					+ "<td></td>"
					+ "</tr>";

			for( i = 0 ; i < contests.number_of_results; i++ ) {
				html += "<tr>"
					+ "<td>" + contests.results[i].title + "</td>"
					+ "<td>" + contests.results[i].description + "</td>"
					+ "<td>" + contests.results[i].start_time + "</td>"
					+ "<td>" + contests.results[i].finish_time + "</td>"
					+ "<td>"
						+ "<button "
						+ " onclick='window.location = \"contest/"+ contests.results[i].alias  +"\"' "
						+ " value='Ver concurso'>Ir al concurso</button></td>"
					+ "</tr>";
			}

			html += "</table>";

			$("#contest_list").removeClass("wait_for_ajax").append(html);
		});
	})();
</script>

