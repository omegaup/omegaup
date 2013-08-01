{include file='redirect.tpl'}
{include file='head.tpl'}
{include file='mainmenu.tpl'}


<div class="post">
	<div class="copy">		
		<div class="POS Boton" id="problem-create">Crear un problema</div>
	</div>
</div>


<div id="parent_problem_list">
	<div class="post">
		<div class="copy wait_for_ajax" id="problem_list" >
		</div>
	</div>
</div>

<script>
	$('#problem-create').click(function() {
		window.location.assign("/problemcreate.php");
	});
</script>

<script>
	(function(){
		function makeWorldClockLink(date) {
			try {
				return "http://timeanddate.com/worldclock/fixedtime.html?iso=" + date.toISOString();
			} catch (e) {
				return '#';
			}
		}
	
		omegaup.getMyProblems(function(problems) {
			// Got the contests, lets draw them

			var html = "<h3>Mis Problemas</h3><table><tr>"
					+ "<td>Título</td>"										
					+ "<td>Público</td>"
					+ "<td></td>"
					+ "<td></td>"
					+ "<td></td>"
					+ "<td></td>"
					+ "<td></td>"
					+ "</tr>";

			for (var i = 0; i < problems.results.length; i++) {
				
				html += "<tr>"
					+ "<td><b><a href='/arena/problem/" + problems.results[i].alias  + "/'>" + problems.results[i].title + "</a></b></td>"										
					+ '<td>'+ ((problems.results[i].public == '1') ? 'Sí' : 'No')  + '</td>'
					+ '<td><a href="/problemedit.php?problem=' + problems.results[i].alias  + '">Editar</a></td>'
					+ '<td><a href="/addproblemtocontest.php?problem=' + problems.results[i].alias  + '">Agregar a concurso</a></td>'					
					+ '<td><a href="/problemstats.php?problem=' + problems.results[i].alias  + '">Estadísticas</a></td>'
					+ "</tr>";
			}

			html += "</table>";

			$("#problem_list").removeClass("wait_for_ajax").append(html);
		});
	})();
</script>
	
{include file='footer.tpl'}