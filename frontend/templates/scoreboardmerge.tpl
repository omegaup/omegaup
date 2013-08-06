{include file='redirect.tpl'}
{include file='head.tpl'}
{include file='mainmenu.tpl'}
{include file='status.tpl'}

<div class="post">
	<div class="copy">
		<legend>Concurso: <select class="contests" name='contests' id='contests' multiple="multiple">				
		</select></legend>
	</div>

	<div class="POS Boton" id="get-merged-scoreboard">Ver scoreboard total</div>
</div>

<div class="post">
	<div class="copy" id="ranking">
		
	</div>
</div>

<script>

	omegaup.getMyContests(function(contests) {					
		// Got the contests, lets populate the dropdown with them			
		for (var i = 0; i < contests.results.length; i++) {
			contest = contests.results[i];							
			$('select.contests').append($('<option></option>').attr('value', contest.alias).text(contest.title));
		}
	});

	$('#get-merged-scoreboard').click(function() {
		contestAliases = $('select.contests option:selected').map(function(){ return this.value }).get();
		omegaup.getScoreboardMerge(contestAliases, function(scoreboard) {
			var html = "<table><tr><td>Username</td>";
			
			for (var alias in contestAliases) {
				html += "<td>" + contestAliases[alias] + "</td>";
				html += "<td> </td>";
			}	
						
			html += "<td> Total </td>";
			html += "<td> Penalty </td>";
			html += "</tr>"
			
			ranking = scoreboard["ranking"];
			for (var entry in ranking) {
				data = ranking[entry];
				html += "<tr>";
				html += "<td>" + data["name"] + "</td>";
				
				for (var contest in data["contests"]) {
					html += "<td>" + data["contests"][contest]["points"] + "</td>";
					html += "<td>" + data["contests"][contest]["penalty"] + "</td>";
				}
				
				html += "<td>" + data["total"]["points"] + "</td>";
				html += "<td>" + data["total"]["penalty"] + "</td>";
				
				html += "</tr>";
			}			
	
			html += "</table>"
			
			$("#ranking").html(html);	

		});
	});
</script>


{include file='footer.tpl'}
