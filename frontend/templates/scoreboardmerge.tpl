{include file='redirect.tpl'}
{include file='head.tpl'}
{include file='mainmenu.tpl'}
{include file='status.tpl'}

<div class="post">
	<div class="copy">
		<legend>Concurso: <select class="contests" name='contests' id='contests' multiple="multiple">				
		</select></legend>
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
</script>


{include file='footer.tpl'}