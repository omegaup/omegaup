{include file='problem_search_bar.tpl'}
<div class="wait_for_ajax panel panel-default" id="problems_list" >
	<div class="panel-heading">
		<h3 class="panel-title">{#wordsProblems#}</h3>
	</div>
	<table class="table">
		<thead>
			<tr>
				<th class="contains-long-desc">{#wordsTitle#}</th>
				<th>{#wordsRuns#}</th>
				<th>{#wordsAccepted#}</th>
				<th>{#wordsRatio#}</th>
				<th>
					{#wordsPointsForRank#}
					<a rel="tooltip" href="http://blog.omegaup.com/2014/06/el-nuevo-ranking-de-omegaup/" data-toggle="tooltip" title data-original-title="{#wordsPointsForRankTooltip#}"><img src="/media/question.png"></a>
				</th>

				<th>{#wordsMyScore#}</th>
			</tr>
		</thead>
		<tbody>
			{foreach item=problem from=$problems}
				<tr>
				<td><a href="/arena/problem/{$problem.alias}">{$problem.title}</a>{if $problem.public == 0} <span class="glyphicon glyphicon-eye-close" title="{#wordsPrivate#}"></span>{/if}</td>
				<td>{$problem.submissions}</td>
				<td>{$problem.accepted}</td>
				<td>{$problem.ratio}%</td>
				<td>{$problem.points}</td>
				<td>{$problem.score}</td>
				</tr>
			{/foreach}
		</tbody>
	</table>

	{include file='pager_bar.tpl'}
</div>
