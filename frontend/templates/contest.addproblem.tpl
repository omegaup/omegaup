{include file='redirect.tpl'}
{assign var="htmlTitle" value="{#omegaupTitleContestAddproblem#}"}
{include file='head.tpl'}
{include file='mainmenu.tpl'}
{include file='status.tpl'}

<div class="panel panel-primary">
	<div class="panel-heading">
		<h2 class="panel-title">{#wordsAddProblem#}</h2>
	</div>
	<div class="panel-body">
		<div class="wait_for_ajax" id="problems_list" >
		</div>
		<form class="form" id="add-problem-form">
			<div class="form-group">
				<label for="problems">{#wordsContests#}</label>
				<select class='form-control' name='contests' id='contests'>
					<option value=""></option>
				</select>
			</div>
			
			<div class="form-group">
				<label for="problems">{#wordsProblems#}</label>
				<select class='form-control' name='problems' id='problems'>
					<option value=""></option>
				</select>
			</div>
			
			<div class="form-group">
				<label for="points">{#contestAddproblemProblemPoints#}</label>
				<input id='points' name='points' size="3" value="100" class="form-control" />
			</div>
			
			<div class="form-group">
				<label for="order">{#contestAddproblemContestOrder#}</label>
				<input id='order' name='order' value='1' size="2" class="form-control" />
			</div>
			
			<div class="form-group">
				<input id='' name='request' value='submit' type='hidden'>
				<button type='submit' class="btn btn-primary">{#wordsAddProblem#}</button>
			</div>
		</form>
		<div class="row">
			<div class="col-md-5">
				<div class="panel panel-default">
					<div class="panel-heading">
						<h3 class="panel-title">{#contestAddproblemRegisteredProblems#}</h3>
					</div>
					<table class="table table-striped">
						<thead>
							<th>{#contestAddproblemContestOrder#}</th>
							<th>{#contestAddproblemProblemName#}</th>
							<th>{#contestAddproblemProblemPoints#}</th>
							<th>{#contestAddproblemProblemRemove#}</th>
						</thead>
						<tbody id="contest-problems"></tbody>
					</table>
				</div>
			</div>
		</div>
	</div>	
</div>
			
<script>	
	(function(){		
		$('#add-problem-form').submit(function() {
			contestAlias = $('select#contests').val();
			problemAlias = $('select#problems').val();
			points = $('input#points').val();
			order = $('input#order').val();
			
			omegaup.addProblemToContest(contestAlias, order, problemAlias, points, function(response){
				if (response.status == "ok") {
					OmegaUp.ui.success("Problem successfully added!");
					$('div.post.footer').show();
					updateContestProblems();
				} else {
					OmegaUp.ui.error(response.error || 'Error');
				}
			});
			
			return false; // Prevent page refresh
		});

		function updateContestProblems() {
			var contestAlias = $('select#contests').val();

			if (contestAlias == '') {
				var problems = $('#contest-problems');
				problems.empty();
				return;
			}

			omegaup.contestProblems(contestAlias, function(response) {
				var problems = $('#contest-problems');
				problems.empty();

				for (var i = 0; i < response.problems.length; i++) {
					problems.append(
						$('<tr></tr>')
							.append($('<td></td>').text(response.problems[i].order))
							.append($('<td></td>').append(
								$('<a></a>')
									.attr('href', '/arena/problem/' + response.problems[i].alias + '/')
									.text(response.problems[i].alias))
							)
							.append($('<td></td>').text(response.problems[i].points))
							.append($('<td><button type="button" class="close">&times;</button></td>')
								.click((function(contest, problem) {
									return function(e) {
										omegaup.removeProblemFromContest(contest, problem, function(response) {
											if (response.status == "ok") {
												OmegaUp.ui.success("Problem successfully removed!");
												$('div.post.footer').show();
												$(e.target.parentElement.parentElement).remove();
											} else {
												OmegaUp.ui.error(response.error || 'error');
											}
										});
									};
								})(contestAlias, response.problems[i].alias))
							)
					);
				}
			});
		}
	
		omegaup.getProblems(function(problems) {					
			// Got the problems, lets populate the dropdown with them			
			for (var i = 0; i < problems.results.length; i++) {
				problem = problems.results[i];							
				$('select#problems').append($('<option></option>').attr('value', problem.alias).text(problem.title));
			}			
			
			omegaup.getMyContests(function(contests) {					
				// Got the contests, lets populate the dropdown with them			
				for (var i = 0; i < contests.results.length; i++) {
					contest = contests.results[i];							
					$('select#contests').append($('<option></option>').attr('value', contest.alias).text(contest.title));
				}

				$('select#contests').change(function () {
					updateContestProblems();
				});

				// If we have a contest in GET, then get it
				{IF isset($smarty.get.contest)}
				$('select#contests').each(function() {
					$('option', this).each(function() {
						if($(this).val() == "{$smarty.get.contest}") {
							$(this).attr('selected', 'selected');
							$('select#contests').trigger('change');
						}
					});
				});
				{/IF}
		
				// If we have a problem in GET, then get it
				{IF isset($smarty.get.problem)}
				$('select#problems').each(function() {
					$('option', this).each(function() {
						if($(this).val() == "{$smarty.get.problem}") {
							$(this).attr('selected', 'selected');
							$('select#problems').trigger('change');
						}
					});
				});
				{/IF}
			});	
						
			$("#problems_list").removeClass("wait_for_ajax");
		});	
		
	})();
</script>
