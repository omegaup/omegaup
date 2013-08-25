{include file='redirect.tpl'}
{include file='head.tpl'}
{include file='mainmenu.tpl'}
{include file='status.tpl'}

<div class="post">
	<div class="panel panel-primary">
		<div class="panel-heading">
			<h3 class="panel-title">Editar Problema</h3>
		</div>
		<div class="panel-body">
			<div class="wait_for_ajax" id="problems_list">
			</div>
			<div>				
				{include file='problem.edit.form.tpl'}
			</div>
		</div>
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
				
			// If we have a problem in GET, then get it
			{IF isset($smarty.get.problem)}
				$('select.edit-problem-list').each(function() {
					$('option', this).each(function() {
						if($(this).val() == "{$smarty.get.problem}") {
							$(this).attr('selected', 'selected');
							$('select.edit-problem-list').trigger('change');
						}
					});
				});
			{/IF}
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
			$('select[name=public]').val(problem.public);
		});
	}
</script>

{include file='footer.tpl'}