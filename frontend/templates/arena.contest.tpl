{include file='arena.head.tpl' jsfile=$jsfile inContest=!$practice}
{include file='arena.runs.tpl'}

{if $admin}
			<script type="text/javascript" src="{version_hash src="/js/omegaup/arena/admin_arena.js"}"></script>
			<audio id="notification_audio">
				<source src="/media/notification.mp3" type="audio/mpeg" />
			</audio>
{/if}
			<div id="title">
				<h1><span class="contest-title"></span><sup class="socket-status" title="WebSocket"></sup></h1>
				<div class="clock">00:00:00</div>
			</div>

{if !$practice}
			<ul class="tabs">
				<li><a href="#problems" class="active">{#wordsProblems#}</a></li>
				<li><a href="#ranking">{#wordsRanking#}</a></li>
{if $admin}
				<li><a href="#runs">{#wordsRuns#}</a></li>
{/if}
				<li><a href="#clarifications">{#wordsClarifications#}<span id="clarifications-count"></span></a></li>
			</ul>
{/if}
			<div id="problems" class="tab navleft">
				<div class="navbar">
					<div id="problem-list">
						<div class="summary">
							<a class="name" href="#problems">{#wordsSummary#}</a>
						</div>
						<div class="template">
							<a class="name"></a>
							<span class="solved"></span>
						</div>
					</div>
{if !$practice}
					<table id="mini-ranking">
						<thead>
							<tr>
								<th></th>
								<th>{#wordsUser#}</th>
								<th class="total" colspan="2">{#wordsTotal#}</th>
							</tr>
						</thead>
						<tbody class="user-list-template">
							<tr>
								<td class="position"></td>
								<td class="user"></td>
								<td class="points"></td>
								<td class="penalty"></td>
							</tr>
						</tbody>
					</table>
{/if}
				</div>
				<div id="summary" class="main">
					<h1 class="title"></h1>
					<p class="description"></p>
					<table>
						<tr><td><strong>{#arenaPracticeStartTime#}</strong></td><td class="start_time"></td></tr>
						<tr><td><strong>{#arenaPracticeEndtime#}</strong></td><td class="finish_time"></td></tr>
						<tr><td><strong>{#arenaPracticeScoreboardCutoff#}</strong></td><td class="scoreboard_cutoff"></td></tr>
						<tr><td><strong>{#arenaContestWindowLength#}</strong></td><td class="window_length"></td></tr>
						<tr><td><strong>{#arenaContestOrganizer#}</strong></td><td class="contest_organizer"></td></tr>
					</table>
				</div>
				<div id="problem" class="main">
					<h1 class="title"></h1>
					<table class="data">
						<tr>
							<td>{#wordsPoints#}</td>
							<td class="points"></div>
							<td>{#arenaCommonMemoryLimit#}</td>
							<td class="memory_limit"></td>
						</tr>
						<tr>
							<td>{#arenaCommonTimeLimit#}</td>
							<td class="time_limit"></td>
							<td>{#arenaCommonOverallWallTimeLimit#}</td>
							<td class="overall_wall_time_limit"></td>
						</tr>
					</table>
{if $admin}
					<form enctype="multipart/form-data" action="/api/problem/update" method="post" id="update-problem">
						<fieldset>
							<legend>Administrar problema</legend>
							<button id="rejudge-problem" value="Rejuecear">Rejuecear</button>
							<input name="problem_alias" type="hidden" />
							<input name="problem_contents" type="file" />
							<button type="submit">Actualizar casos/redacci&oacute;n</button>
						</fieldset>
					</form>
{/if}
					<div class="karel-js-link hide">
						<a href="/karel.js/" target="_blank">{#openInKarelJs#} <span class="glyphicon glyphicon-new-window"></span></a>
					</div>
					<div class="statement"></div>
					<hr />
					<div class="source">{#wordsSource#}: <span></span></div>
					<div class="problemsetter">{#wordsProblemsetter#}: <a></a></div>
{if $practice}
					<runs-table class="runs"
								params="view: view,
										options: { showSubmit: true, showDetails: true}">
					</runs-table>
{else}
					<runs-table class="runs"
								params="view: view,
										options: { showPoints: true,
												   showSubmit: true,
												   showDetails: true }">
					</runs-table>
{/if}
				</div>
			</div>
{if $admin}
			<div id="runs" class="tab">
				<runs-table params="view: view,
									options: { showPager: true,
											   showPoints: true,
											   showUser: true,
											   showProblem: true,
											   showRejudge: true,
											   showDetails: true }">
				</runs-table>
			</div>
{/if}
			<div id="ranking" class="tab">
				<div id="ranking-chart"></div>
				<table id="ranking-table">
					<thead>
						<tr>
							<th></th>
							<th></th>
							<th>{#wordsUser#}</th>
							<th class="total" colspan="2">{#wordsTotal#}</th>
						</tr>
					</thead>
					<tbody class="user-list-template">
						<tr>
							<td class="position"></td>
							<td class="legend"></td>
							<td class="user"></td>
							<td class="points"></td>
							<td class="penalty"></td>
						</tr>
					</tbody>
				</table>
				<div class="footer"></div>
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
							<th class="problem">{#wordsProblem#}</th>
							<th class="author">{#wordsAuthor#}</th>
							<th class="time">{#wordsTime#}</th>
							<th class="message">{#wordsMessage#}</th>
							<th class="answer">{#wordsResult#}</th>
						</tr>
					</thead>
					<tfoot>
						<tr>
							<td colspan="6"><a href="#clarifications/new">{#wordsNewClarification#}</a></td>
						</tr>
					</tfoot>
					<tbody class="clarification-list">
						<tr class="template">
							<td class="problem"></td>
							<td class="author"></td>
							<td class="time"></td>
							<td><pre class="message"></pre></td>
							<td class="answer">
								<pre></pre>
								<form id="create-response-form" class="form-inline template">
									<textarea id="create-response-text" class="form-control" placeholder="{#wordsAnswer#}"></textarea>
									<label><input type="checkbox" id="create-response-is-public"/> {#wordsPublic#}</label>
									<input type="submit" />
								</form>
							</td>
						</tr>
					</tbody>
				</table>
			</div>
		</div>
		<div id="overlay">
{include file='arena.runsubmit.tpl'}
			<form id="clarification" method="POST">
				<button class="close">&times;</button>
				{#wordsProblem#}
				<select name="problem">
				</select><br/>
				<label for="message">{#arenaClarificationCreateMaxLength#}</label>
				<textarea name="message" maxlength="200"></textarea><br/>
				<input type="submit" />
			</form>
{include file='arena.rundetails.tpl'}
		</div>
	</body>
</html>
