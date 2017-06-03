{include file='problem_search_bar.tpl'}
<div class="wait_for_ajax panel panel-default" id="problems_list" >
	<div class="panel-heading">
		<h3 class="panel-title">{#wordsProblems#}</h3>
	</div>
	<table class="table problem-list">
		<thead>
			<tr>
				<th class="contains-long-desc">{#wordsTitle#}</th>
				<th class="numericColumn">{#wordsRuns#}</th>
				<th class="numericColumn">{#wordsAccepted#}</th>
				<th class="numericColumn">{#wordsRatio#}</th>
				<th class="numericColumn">
					{#wordsPointsForRank#}
					<a rel="tooltip" href="http://blog.omegaup.com/2014/06/el-nuevo-ranking-de-omegaup/" data-toggle="tooltip" title data-original-title="{#wordsPointsForRankTooltip#}"><img src="/media/question.png"></a>
				</th>
				<th class="numericColumn">{#wordsMyScore#}</th>
			</tr>
		</thead>
		<tbody>
			{foreach item=problem from=$problems}
				<tr {if $problem.visibility >= 2}class="high-quality"{/if}>
				<td>
					{if $problem.visibility < 0} <span class="glyphicon glyphicon-ban-circle" title="{#wordsBannedProblem#}"></span>{/if}
					{if $problem.visibility == 0} <span class="glyphicon glyphicon-eye-close" title="{#wordsPrivate#}"></span>{/if}
					{if $problem.visibility >= 2} <img src="/media/quality-badge-sm.png" title="{#wordsHighQualityProblem#}"></img>{/if}
					<a href="/arena/problem/{$problem.alias}">{$problem.title}</a>
					{if count($problem.tags) > 0}
					<div class="tag-list" title="{" "|implode:$problem.tags|escape}">
					{foreach item=tag from=$problem.tags}
						<a class="tag" href="/problem/?tag={$tag|escape}">{$tag|escape}</a>
					{/foreach}
					</div>
					{/if}
				</td>
				<td class="numericColumn">{$problem.submissions}</td>
				<td class="numericColumn">{$problem.accepted}</td>
				<td class="numericColumn">{100 * $problem.ratio}%</td>
				<td class="numericColumn">{$problem.points}</td>
				<td class="numericColumn">{$problem.score}</td>
				</tr>
			{/foreach}
		</tbody>
	</table>

	{include file='pager_bar.tpl'}
</div>
