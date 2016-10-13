{include file='arena.head.tpl' jsfile={version_hash src='/ux/contest.js'} bodyid='only-problem'}
{include file='arena.runs.tpl'}
			{if $problem_admin}
			<script type="text/javascript" src="{version_hash src="/js/omegaup/arena/admin_arena.js"}"></script>
			<ul class="tabs">
				<li><a href="#problems" class="active">{#wordsProblem#}</a></li>
				<li><a href="#runs">{#wordsRuns#}</a></li>
				<li><a href="#clarifications">{#wordsClarifications#}<span id="clarifications-count"></span></a></li>
			</ul>
			{/if}
			<div id="problems" class="tab">
				<div id="problem" class="main">
					<script type="text/json" id="problem-json">{$problem}</script>
					<h1 class="title">{if $public == 0}<span class="glyphicon glyphicon-eye-close" title="{#wordsPrivate#}"></span>{/if} {$title|escape}
						{if $problem_admin}
							(<a href="/problem/{$problem_alias}/edit/">{#wordsEdit#}</a>)
						{/if}
					</h1>
					<table class="data">
						<tr>
							<td>{#wordsPoints#}</td>
							<td class="points">{$points|escape}</div>
							<td>{#wordsMemoryLimit#}</td>
							<td class="memory_limit">{$memory_limit|escape}</td>
						</tr>
						<tr>
							<td>{#wordsTimeLimit#}</td>
							<td class="time_limit">{$time_limit|escape}</td>
							<td>{#wordsOverallWallTimeLimit#}</td>
							<td class="overall_wall_time_limit">{$overall_wall_time_limit|escape}</td>
						</tr>
					</table>
{if $karel_problem}
					<div class="karel-js-link">
						<a href="/karel.js/{if !empty($sample_input)}#mundo:{$sample_input|escape:url}{/if}" target="_blank">{#openInKarelJs#} <span class="glyphicon glyphicon-new-window"></span></a>
					</div>
{/if}
					<div class="statement">{$problem_statement}</div>
					<hr />
{if $source}
					<div class="source">{#wordsSource#}: <span>{$source|escape}</span></div>
{/if}
{if $problemsetter}
					<div class="problemsetter">{#wordsProblemsetter#}: <a href="/profile/{$problemsetter.username}/">{$problemsetter.name|escape}</a></div>
{/if}
					<runs-table id="user-runs"
								params="view: view,
										options: { showSubmit: true, showDetails: true }">
					</runs-table>
					<table class="best-solvers">
						<caption>{#wordsBestSolvers#}</caption>
						<thead>
							<tr>
								<th>{#wordsUser#}</th>
								<th>{#wordsLanguage#}</th>
								<th>{#wordsMemory#}</th>
								<th>{#wordsRuntime#}</th>
								<th>{#wordsTime#}</th>
							</tr>
						</thead>
						<tbody class="solver-list">
							<tr class="template">
								<td><a class="user"></a></td>
								<td class="language"></td>
								<td class="memory"></td>
								<td class="runtime"></td>
								<td class="time"></td>
							</tr>
						</tbody>
					</table>
				</div>
			</div>
			{if $problem_admin}
			<div id="runs" class="tab">
				<runs-table params="view: view,
									options: {
										showPager: true,
										showUser: true,
										showRejudge: true,
										showDetails: true }">
				</runs-table>
			</div>
			<div id="clarifications" class="tab">
				<table class="clarifications">
					<caption>
						{#wordsClarifications#}
						<div class="clarifpager">
							<button class="clarifpagerprev">&lt;</button>
							<button class="clarifpagernext">&gt;</button>
						</div>
					</caption>
					<thead>
						<tr>
							<th class="contest">{#wordsContest#}</th>
							<th class="author">{#wordsAuthor#}</th>
							<th class="time">{#wordsTime#}</th>
							<th class="message">{#wordsMessage#}</th>
							<th class="answer">{#wordsResult#}</th>
						</tr>
					</thead>
					<tbody class="clarification-list">
						<tr class="template">
							<td class="contest"></td>
							<td class="author"></td>
							<td class="time"></td>
							<td><pre class="message"></pre></td>
							<td class="answer"><pre></pre></td>
						</tr>
					</tbody>
				</table>
			</div>
			{/if}
		</div>
		<div id="overlay">
{include file='arena.runsubmit.tpl'}
{include file='arena.rundetails.tpl'}
		</div>
		<div id="footer">
		</div>
	</body>
</html>
