{include file='arena.head.tpl' jsfile='/ux/arena.js'}
			<div class="container" id="main">
				<div class="panel panel-primary">
					<div class="panel-heading">
						<h2 class="panel-title">Concursos actuales</h2>
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
		<script type="text/javascript">
		var _gaq = _gaq || [];
		_gaq.push(['_setAccount', '{$OMEGAUP_GA_ID}']);
		_gaq.push(['_trackPageview']);
		(function() {
		var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
		ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
		var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
		})();
		</script>
		{/if}
	</body>
</html>
