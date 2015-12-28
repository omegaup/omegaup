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
				<table class="table" id="problem-list">
					<thead>
						<tr>
							<th></th>
							<th>{#wordsTitle#}</th>
							<th>{#wordsEdit#}</th>
							<th>{#wordsStatistics#}</th>
						</tr>
					</thead>
					<tbody class="problem-list-template">
						<tr>
							<td>
								<input type="checkbox"></td>
							<td>
								<a class="title"></a> <span class="glyphicon glyphicon-eye-close private hidden" title="{#wordsPrivate#}"></span>
								<div class="tag-list hidden"></div>
							</td>
							<td>
								<a class="glyphicon glyphicon-edit edit"></a>
							</td>
							<td>
								<a class="glyphicon glyphicon-stats stats"></a>
							</td>
						</tr>
					</tbody>
				</table>
			</div>
		</div>
	</div>
</div>
<script type="text/javascript" src="/js/problem.mine.js?ver=c46c26"></script>
{include file='footer.tpl'}
