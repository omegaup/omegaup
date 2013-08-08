{include file='redirect.tpl'}
{include file='head.tpl'}
{include file='mainmenu.tpl'}
{include file='status.tpl'}

<div class="post">
	<div class="copy">
		<legend>Concurso: <select class="contests" name='contests' id='contests' multiple="multiple" size="10">				
		</select></legend>
	</div>

	<div class="POS Boton" id="get-merged-scoreboard">Ver scoreboard total</div>
</div>

<div class="post">
	<div class="copy" id="ranking">
		
	</div>
</div>

<script>

	omegaup.getContests(function(contests) {					
		// Got the contests, lets populate the dropdown with them			
		for (var i = 0; i < contests.results.length; i++) {
			contest = contests.results[i];							
			$('select.contests').append($('<option></option>').attr('value', contest.alias).text(contest.title));
		}
	});

	$('#get-merged-scoreboard').click(function() {
		contestAliases = $('select.contests option:selected').map(function(){ return this.value }).get();
		omegaup.getScoreboardMerge(contestAliases, function(scoreboard) {
			var html = "<table class=\"merged-scoreboard\"><tr><td></td><td><b>Username</b></td>";
			
			var contests = [];
			for (var alias in scoreboard["ranking"][0]["contests"]) {
				html += "<td><b>" + alias + "</b></td>";
				html += "<td> </td>";
				contests.push(alias);
			}	
						
			html += "<td><b>Total</b></td>";
			html += "<td><b>Penalty</b></td>";
			html += "</tr>"
			
			ranking = scoreboard["ranking"];
			for (var entry in ranking) {
				
				data = ranking[entry];
				place = parseInt(entry) + 1;
				
				html += "<tr>";
				html += "<td><b>" + (place) + "</b></td>" 
				html += "<td>" + data["username"] + " (" + data["name"] + ")</td>";
				
				for (var c in contests) {
					html += "<td>" + data["contests"][contests[c]]["points"] + "</td>";
					html += "<td>" + data["contests"][contests[c]]["penalty"] + "</td>";
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
