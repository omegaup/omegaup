{include file='redirect.tpl'}
{include file='head.tpl'}
{include file='mainmenu.tpl'}
{include file='status.tpl'}

<div class="post">
	<div class="copy wait_for_ajax" id="problems_list" >
	</div>
	<div class="copy">						
		<legend>Concursos: <select class='contests' name='contests' id='contest_alias'>
			<option value=""></option>				
		</select></legend>

		<legend>Problemas: <select class='problems' name='problems' id='problem_alias'>
			<option value=""></option>				
		</select></legend>
		
		<legend> Puntos que vale el problema: <input id='points' name='points' class='points' size="3" value="100">
		</legend>
		
		<legend> Orden en el concurso: <input id='order' name='order' class='order' value='1' size="2">
		</legend>

		<input id='' name='request' value='submit' type='hidden'>
		<input value='Agregar problema' type='submit' class="OK">		
	</div>	
</div>
			
<script>	
	(function(){		
	
		$('input.OK').click(function() {
			contestAlias = $('select.contests').val();
			problemAlias = $('select.problems').val();
			points = $('input[name=points]').val();
			order = $('input[name=order]').val();
			
			omegaup.addProblemToContest(contestAlias, order, problemAlias, points, function(response){
				if (response.status == "ok") {
					$('div.copy.error').html("Problem successfully added!");
					$('div.post.footer').show();
					return;
				}
			
				
			});
		});
	
		omegaup.getProblems(function(problems) {					
			// Got the problems, lets populate the dropdown with them			
			for (var i = 0; i < problems.results.length; i++) {
				problem = problems.results[i];							
				$('select.problems').append($('<option></option>').attr('value', problem.alias).text(problem.title));
			}			
			
			omegaup.getMyContests(function(contests) {					
				// Got the contests, lets populate the dropdown with them			
				for (var i = 0; i < contests.results.length; i++) {
					contest = contests.results[i];							
					$('select.contests').append($('<option></option>').attr('value', contest.alias).text(contest.title));
				}
				
				// If we have a contest in GET, then get it
				{IF isset($smarty.get.contest)}
				$('select.contests').each(function() {
					$('option', this).each(function() {
						if($(this).val() == "{$smarty.get.contest}") {
							$(this).attr('selected', 'selected');
							$('select.contests').trigger('change');
						}
					});
				});
				{/IF}
			});	
						
			$("#problems_list").removeClass("wait_for_ajax");
		});	
		
	})();
</script>