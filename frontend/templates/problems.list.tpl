<div class="wait_for_ajax panel panel-default" id="problems_list" >
	<div class="panel-heading">
		<h3 class="panel-title">{#wordsProblems#}</h3>
	</div>
	<table class="table">
		<thead>
			<tr>
				<th class="contains-long-desc">{#wordsTitle#}</th>
				<th>{#wordsRuns#}</th>
				<th>{#wordsSolved#}</th>
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
				<td><a href="/arena/problem/{$problem.alias}">{$problem.title}</a></td>
				<td>{$problem.submissions}</td>
				<td>{$problem.accepted}</td>
				<td>{$problem.ratio}%</td>
				<td>{$problem.rankPoints}</td>
				<td>{$problem.score}</td>
				</tr>
			{/foreach}
		</tbody>
	</table>

	<div class="pager-bar">
		<center>
			<ul class="pagination">
				{foreach from=$pager_links item=page}
					<li {if $page.class != ''}class="{$page.class}"{/if}>
						<a href="{$page.url}">{$page.label}</a>
					</li>
				{/foreach}
			</ul>
		</center>
	</div>
</div>
<!--<script src="/js/problems.list.js"></script>-->
