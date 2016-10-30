<div class="wait_for_ajax panel panel-default no-bottom-margin" id="contest_list">

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

	<div class="panel-heading">
		<h3 class="panel-title">{#wordsContests#}</h3>
	</div>

	<table class="table">
		<thead>
			<th></th>
			<th>{#wordsTitle#}</th>
			<th>{#arenaPracticeStartTime#}</th>
			<th>{#arenaPracticeEndtime#}</th>
			<th>{#contestsTablePublic#}</th>
			<th colspan="2">Scoreboard</th>
			<th></th>
			<th></th>
			<th></th>
			<th></th>
			<th></th>
		</thead>
		<tbody>
		</tbody>
	</table>
</div>
<script src="{version_hash src="/js/contest.list.js"}" type="text/javascript"></script>
