{include file='head.tpl' jsfile={version_hash src='/ux/arena.js' inArena=true}}
{include file='arena.contest_list.tpl'}
				<div id="content">
					<div class="panel">
						<div class="panel-heading panel-default">
							<div class="text-right">
								<form action="/arena/" method="GET">
									<div class="form-inline">
										<div class="form-group">
											<input class="form-control"
													type="text" name="query" autocomplete="off"
													{if $query != ''} value="{$query|escape}"{/if}
													placeholder="{#wordsKeyword#}">
										</div>
										<input class="btn btn-primary btn-lg active" type="submit" value="{#wordsSearch#}"/>
									</div>
								</form>
							</div>
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
									<a class="nav-link" href="#list-current-public-contest" data-toggle="tab">
										{#arenaCurrentPublicContests#}</a>
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
								{if $LOGGED_IN == 1}
								<li class="nav-item">
									<a class="nav-link" href="#list-current-participating-contest" data-toggle="tab">
										{#arenaMyActiveContests#}</a>
								</li>
								{/if}
							</ul>

							<div class="tab-content">
								<div class="tab-pane" id="list-recommended-current-contest">
									<div class="panel panel-primary" id="recommended-current-contests"
										 data-bind="template: { name: 'contest-list', if: page().length > 0 }"></div>
								</div>
								<div class="tab-pane" id="list-current-participating-contest">
									<div class="panel panel-primary" id="participating-current-contests"
										 data-bind="template: { name: 'contest-list', if: page().length > 0 }"></div>
								</div>
								<div class="tab-pane" id="list-current-contest">
									<div class="panel panel-primary" id="current-contests"
										 data-bind="template: { name: 'contest-list', if: page().length > 0 }"></div>
								</div>
								<div class="tab-pane" id="list-current-public-contest">
									<div class="panel panel-primary" id="current-public-contests"
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
		</div> <!-- root -->
{include file='common.analytics.tpl'}
	</body>
</html>
