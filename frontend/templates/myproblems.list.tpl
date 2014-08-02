{include file='redirect.tpl'}
{assign var="htmlTitle" value="{#omegaupTitleMyProblemsList#}"}
{include file='head.tpl'}
{include file='mainmenu.tpl'}
{include file='status.tpl'}

{if $LOGGED_IN eq 1 and $CURRENT_USER_PRIVATE_PROBLEMS_COUNT gt 0 and $PRIVATE_PROBLEMS_ALERT eq 1}
	<div class="alert alert-info">
		<span class="message">
			{#messageMakeYourProblemsPublic#}
		</span>
	</div>
{/if}


<div class="panel panel-default">
	<div class="panel-body">
		<div class="bottom-margin">
			<a href="/problem/new/" class="btn btn-primary" id="problem-create">{#myproblemsListCreateProblem#}</a>
		</div>
		<div class="bottom-margin">
			{#forSelectedItems#}: 
			<div class="btn-group">
				<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
				  {#selectAction#}<span class="caret"></span>
				</button>
				<ul class="dropdown-menu" role="menu">
				  <li><a id="bulk-make-public">{#makePublic#}</a></li>
				  <li><a id="bulk-make-private">{#makePrivate#}</a></li>				  
				  <li class="divider"></li>				  
				</ul>
			  </div>
		</div>
		<div id="parent_problem_list">
			<div class="wait_for_ajax panel panel-default no-bottom-margin" id="problem_list">
				<div class="panel-heading">
					<h3 class="panel-title">{#myproblemsListMyProblems#}</h3>
				</div>
				<table class="table">
					<thead>
						<tr>
							<th></th>
							<th>{#wordsTitle#}</th>
							<th>{#contestNewFormPublic#}</th>
							<th></th>
							<th></th>
						</tr>
					</thead>
					<tbody></tbody>
				</table>
			</div>
		</div>
	</div>
</div>

<script>
	(function(){
		$(".navbar #nav-problems").addClass("active");
		
		function makeWorldClockLink(date) {
			try {
				return "http://timeanddate.com/worldclock/fixedtime.html?iso=" + date.toISOString();
			} catch (e) {
				return '#';
			}
		}
	
		function fillProblemsTable() {
			omegaup.getMyProblems(function(problems) {
				// Got the contests, lets draw them
				var html = "";
				for (var i = 0; i < problems.results.length; i++) {
					html += "<tr>"
						+ "<td><input type='checkbox' id='" + problems.results[i].alias + "'/></td>" 
						+ "<td><b><a href='/arena/problem/" + problems.results[i].alias  + "/'>" + omegaup.escape(problems.results[i].title) + "</a></b></td>"										
						+ '<td>'+ ((problems.results[i].public == '1') ? 'Sí' : 'No')  + '</td>'
						+ '<td><a class="glyphicon glyphicon-edit" href="/problem/edit/' + problems.results[i].alias  + '/"></a></td>'
						+ '<td><a class="glyphicon glyphicon-stats" href="/problem/stats/' + problems.results[i].alias  + '/"></a></td>'
						+ "</tr>";
				}
				$("#problem_list").removeClass("wait_for_ajax")
				$("#problem_list tbody").empty().html(html);
			});
		}
		fillProblemsTable();
		
		$("#bulk-make-public").click(function() {
			OmegaUp.ui.bulkOperation(
				function(alias, handleResponseCallback) {
					omegaup.updateProblem(alias, 1 /*public*/, handleResponseCallback);
				},
				function() {
					fillProblemsTable();
				}
			)}
		);
		
		$("#bulk-make-private").click(function() {
			OmegaUp.ui.bulkOperation(
				function(alias, handleError) {
					omegaup.updateProblem(alias, 0 /*public*/, handleResponseCallback);
				},
				function() {
					fillProblemsTable();
				}
			)}
		);
	})();
</script>
	
{include file='footer.tpl'}
