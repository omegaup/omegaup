{include file='arena.head.tpl' jsfile='/ux/arena.js?ver=3ea0f6'}
{include file='arena.contest_list.tpl' jsfile='/omegaup/arena/contest_list.js?ver=62c67a'}
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

				<div class="panel panel-primary" id="recommended-current-contests"
				     data-bind="template: 'contest-list'"></div>
				<div class="panel panel-primary" id="current-contests"
				     data-bind="template: 'contest-list'"></div>
				<div class="panel panel-primary" id="recommended-past-contests"
				     data-bind="template: 'contest-list'"></div>
				<div class="panel panel-primary" id="past-contests"
				     data-bind="template: 'contest-list'"></div>
			</div>
		</div>
		{if $OMEGAUP_GA_TRACK eq 1}
		<script type="text/javascript" src="/js/google-analytics.js?ver=bc0b14"></script>
		{/if}
	</body>
</html>
