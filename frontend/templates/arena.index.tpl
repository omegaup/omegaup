{include file='arena.head.tpl' jsfile='/ux/arena.js?ver=9a49de'}
			<div class="container" id="main">
				<div class="panel panel-default">
					<div class="panel-body">
						<h1>{#arenaPageTitle#}</h1>
						<p>{#arenaPageIntroduction#}</p>
						<p>{#arenaPageRecommendedContestsText#}</p>

						<p>{#frontPageIntroduction#}</p>
						<div class="text-center">
							<a href="http://blog.omegaup.com/category/omegaup/omegaup-101/" class="btn btn-primary btn-lg">{#frontPageIntroductionButton#}</a>
						</div>
					</div>
				</div>

				<div class="panel panel-primary">
					<div class="panel-heading">
						<h2 class="panel-title">{#arenaRecommendedCurrentContests#}</h2>
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
						<tbody id="recommended-current-contests">
						</tbody>
					</table>
				</div>

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
						<h2 class="panel-title">{#arenaRecommendedOldContests#}</h2>
					</div>
					<table class="contest-list table table-striped table-hover">
						<thead><tr>
							<th>{#wordsContest#}</th>
							<th>{#wordsDescription#}</th>
							<th>{#wordsPractice#}</th>
						</tr></thead>
						<tbody id="recommended-past-contests">
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
							<th>{#wordsPractice#}</th>
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
