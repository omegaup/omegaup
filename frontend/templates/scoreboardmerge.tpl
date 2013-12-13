{include file='redirect.tpl'}
{assign var="htmlTitle" value="{#omegaupTitleScoreboardmerge#}"}
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
				html += "<td colspan=\"2\"><b>" + alias + "</b></td>";
				contests.push(alias);
			}	
						
			html += "<td colspan=\"2\"><b>{#wordsTotal#}</b></td>";
			html += "</tr>"
			
			ranking = scoreboard["ranking"];
			var showPenalty = false;
			for (var entry in ranking) {
				if (!ranking.hasOwnProperty(entry)) continue;
				data = ranking[entry];
				showPenalty |= !!data["total"]["penalty"];
			}

			for (var entry in ranking) {
				if (!ranking.hasOwnProperty(entry)) continue;
				data = ranking[entry];
				place = parseInt(entry) + 1;
				
				html += "<tr>";
				html += "<td><strong>" + (place) + "</strong></td>";
				html += "<td><div class=\"username\">" + data["username"] + "</div>";
				if (data["username"] != data["name"]) {
					html += "<div class=\"name\">" + data["name"] + "</div></td>";
				} else {
					html += "<div class=\"name\">&nbsp;</div></td>";
				}
				
				for (var c in contests) {
					if (showPenalty) {
						html += "<td class=\"numeric\">" + data["contests"][contests[c]]["points"] + "</td>";
						html += "<td class=\"numeric\">" + data["contests"][contests[c]]["penalty"] + "</td>";
					} else {
						html += "<td class=\"numeric\" colspan=\"2\">" + data["contests"][contests[c]]["points"] + "</td>";
					}
				}
				
				if (showPenalty) {
					html += "<td class=\"numeric\">" + data["total"]["points"] + "</td>";
					html += "<td class=\"numeric\">" + data["total"]["penalty"] + "</td>";
				} else {
					html += "<td class=\"numeric\" colspan=\"2\">" + data["total"]["points"] + "</td>";
				}
				
				html += "</tr>";
			}			
	
			html += "</table>"
			
			$("#ranking").html(html);	
		});
	});
</script>


{include file='footer.tpl'}
