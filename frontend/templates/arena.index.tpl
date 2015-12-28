{include file='arena.head.tpl' jsfile='/ux/arena.js?ver=ae8e3f'}
			<div class="container" id="main">
				<div class="panel panel-primary">
					<div class="panel-heading">
						<h2 class="panel-title">{#arenaCurrentContests#}</h2>
					</div>
					<table class="contest-list table table-striped table-hover">
						<thead><tr>
							<th>{#wordsContest#}</th>
							<th>{#wordsDescription#}</th>
							<th class="time">{#wordsStartTime#}</th>
							<th class="time">{#wordsEndTime#}</th>
							<th></th>
							<th></th>
						</tr></thead>
						<tbody id="current-contests">
						</tbody>
					</table>
				</div>

				<div class="panel panel-default">
					<div class="panel-heading">
						<h2 class="panel-title">{#arenaOldContests#}</h2>
					</div>
					<table class="contest-list table table-striped table-hover">
						<thead><tr>
							<th>{#wordsContest#}</th>
							<th>{#wordsDescription#}</th>
							<th class="time">{#wordsStartTime#}</th>
							<th class="time">{#wordsEndTime#}</th>
							<th></th>
							<th></th>
						</tr></thead>
						<tbody id="past-contests">
						</tbody>
					</table>
				</div>
			</div>
		</div>
		{if $OMEGAUP_GA_TRACK eq 1}
		<script type="text/javascript" src="/js/google-analytics.js"></script>
		{/if}
	</body>
</html>
