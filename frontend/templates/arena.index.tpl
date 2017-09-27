{include file='head.tpl' jsfile={version_hash src='/ux/arena.js' inArena=true}}
{include file='arena.contest_list.tpl'}
			<div id="content">
				<div class="panel">
					<div class="panel-heading panel-default">
						<h1>{#arenaPageTitle#}</h1>
						<p>{#arenaPageIntroduction#}</p>
						<p>{#frontPageIntroduction#}
							<a href="http://blog.omegaup.com/category/omegaup/omegaup-101/" target="_blank">
								<small><u>{#frontPageIntroductionButton#}</u></small></a></p>
					</div>
					<div class="panel-body">

						<ul class="nav nav-pills arena-tabs">
							<li class="nav-item">
								<a class="nav-link" href="#list-recommended-current-contest" data-toggle="tab">
									{#arenaRecommendedCurrentContests#}</a>
							</li>
							<li class="nav-item">
								<a class="nav-link" href="#list-current-contest" data-toggle="tab">
									{#arenaCurrentContests#}</a>
							</li>
							<li class="nav-item">
								<a class="nav-link" href="#list-future-contest" data-toggle="tab">
									{#arenaFutureContests#}</a>
							</li>
							<li class="nav-item">
								<a class="nav-link" href="#list-recommended-past-contest" data-toggle="tab">
									{#arenaRecommendedOldContests#}</a>
							</li>
							<li class="nav-item">
								<a class="nav-link" href="#list-past-contest" data-toggle="tab">
									{#arenaOldContests#}</a>
							</li>
						</ul>

						<div class="tab-content">
							<div class="tab-pane" id="list-recommended-current-contest">
								<div class="panel panel-primary" id="recommended-current-contests"
									 data-bind="template: { name: 'contest-list', if: page().length > 0 }"></div>
							</div>
							<div class="tab-pane" id="list-current-contest">
								<div class="panel panel-primary" id="current-contests"
									 data-bind="template: { name: 'contest-list', if: page().length > 0 }"></div>
							</div>
							<div class="tab-pane" id="list-future-contest">
								<div class="panel panel-primary" id="future-contests"
									 data-bind="template: { name: 'contest-list', if: page().length > 0 }"></div>
							</div>
							<div class="tab-pane" id="list-recommended-past-contest">
								<div class="panel panel-primary" id="recommended-past-contests"
									 data-bind="template: { name: 'contest-list', if: page().length > 0 }"></div>
							</div>
							<div class="tab-pane" id="list-past-contest">
								<div class="panel panel-primary" id="past-contests"
									 data-bind="template: { name: 'contest-list', if: page().length > 0 }"></div>
							</div>
						</div>

					</div> <!-- panel-body -->
				</div> <!-- panel -->
			</div> <!-- panel-default -->
		</div> <!-- container -->
		{if $OMEGAUP_GA_TRACK eq 1}
		<script type="text/javascript" src="{version_hash src="/js/google-analytics.js"}"></script>
		{/if}
	</body>
</html>
