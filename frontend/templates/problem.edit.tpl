{include file='redirect.tpl'}
{include file='head.tpl'}
{include file='mainmenu.tpl'}
{include file='status.tpl'}

<div class="post">
	<div class="copy wait_for_ajax" id="problems_list" >
	</div>
	<div class="copy">				
		{include file='problem.edit.form.tpl'}
	</div>	
</div>
			
<script>
	(function(){		
	
		$('select.edit-problem-list').change(function () {			
			console.log("changed select");
			refreshEditForm($('select.edit-problem-list option:selected').val());
		});
	
		omegaup.getMyProblems(function(problems) {					
			// Got the problems, lets populate the dropdown with them			
			for (var i = 0; i < problems.results.length; i++) {
				problem = problems.results[i];							
				$('select.edit-problem-list').append($('<option></option>').attr('value', problem.alias).text(problem.title));
			}			

			$("#problems_list").removeClass("wait_for_ajax");
		});
	})();
	
	function refreshEditForm(problemAlias) {
		
		if (problemAlias === "") {
			$('input[name=title]').val('');
			$('input[name=time_limit]').val('');
			$('input[name=memory_limit]').val('');
			$('input[name=source]').val('');
			return;
		}
		
		omegaup.getProblem(null, problemAlias, function(problem) {
			$('input[name=title]').val(problem.title);
			$('input[name=time_limit]').val(problem.time_limit);
			$('input[name=memory_limit]').val(problem.memory_limit);
			$('input[name=source]').val(problem.source);
			$('select[name=validator]').val(problem.validator);
		});
	}
	
</script>

{include file='footer.tpl'}