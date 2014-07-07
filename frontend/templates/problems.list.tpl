<div class="wait_for_ajax panel panel-default" id="problems_list" >
	<div class="panel-heading">
		<h3 class="panel-title">{#wordsProblems#}</h3>
	</div>
	<table class="table">
		<thead>
			<tr>
				<th class="contains-long-desc">{#wordsTitle#}</th>
				<th>
					<a rel="tooltip" href="?sort=runs" data-toggle="tooltip" title data-original-title="{#sortByRuns#}">{#wordsRuns#}</a>
				</th>
				<th>
					<a rel="tooltip" href="?sort=solved" data-toggle="tooltip" title data-original-title="{#sortBySolved#}">{#wordsSolved#}</a>
				</th>
				<th>{#wordsRatio#}</th>
				<th>
					<a rel="tooltip" href="?sort=solved" data-toggle="tooltip" title data-original-title="{#sortByPointsForRank#}">{#wordsPointsForRank#}</a>
					<a rel="tooltip" href="http://blog.omegaup.com/2014/06/el-nuevo-ranking-de-omegaup/" data-toggle="tooltip" title data-original-title="{#wordsPointsForRankTooltip#}"><img src="/media/question.png"></a>
				</th>
				<th>
					<a rel="tooltip" href="?sort=solved" data-toggle="tooltip" title data-original-title="{#sortByMyScore#}">{#wordsMyScore#}</a>
				</th>
			</tr>
		</thead>
		<tbody></tbody>
	</table>
</div>
<script src="/js/problems.list.js"></script>
