{include file='head.tpl' jsfile=$jsfile inContest=$showNavigation inArena=true inline}

{if $admin}
			<audio id="notification-audio">
				<source src="/media/notification.mp3" type="audio/mpeg" />
			</audio>
{/if}
			<div id="title">
				<h1><span class="contest-title"></span><sup class="socket-status" title="WebSocket"></sup></h1>
				<div class="clock">00:00:00</div>
			</div>

{if $showNavigation}
			<ul class="tabs">
				<li><a href="#problems" class="active">{#wordsProblems#}</a></li>
{if $showRanking}
				<li><a href="#ranking">{#wordsRanking#}</a></li>
{/if}
{if $admin}
				<li><a href="#runs">{#wordsRuns#}</a></li>
{/if}
{if $showClarifications}
				<li><a href="#clarifications">{#wordsClarifications#}<span id="clarifications-count"></span></a></li>
{/if}
			</ul>
{/if}
			<div id="problems" class="tab navleft">
				<div class="navbar">
					<div id="arena-navbar-problems"></div>
					<div id="arena-navbar-miniranking"></div>
					<script type="text/json" id="arena-navbar-payload">{$showRanking|json_encode}</script>
				</div>
				<div id="summary" class="main">
					<h1 data-bind="text: title"></h1>
					<p data-bind="text: description"></p>
					<table>
{if $showDeadlines}
						<tr><td><strong>{#arenaPracticeStartTime#}</strong></td><td data-bind="text: startTime"></td></tr>
						<tr><td><strong>{#arenaPracticeEndtime#}</strong></td><td data-bind="text: finishTime"></td></tr>
{/if}
{if $showRanking}
						<tr><td><strong>{#arenaPracticeScoreboardCutoff#}</strong></td><td data-bind="text: scoreboardCutoff"></td></tr>
{/if}
						<tr><td><strong>{#arenaContestWindowLength#}</strong></td><td data-bind="text: windowLength"></td></tr>
						<tr>
							<td><strong>{#arenaContestOrganizer#}</strong></td>
							<td>
								<a data-bind="text: contestOrganizer, attr: { href: '/profile/' + contestOrganizer + '/' }"></a>
							</td>
						</tr>
					</table>
				</div>
				<div id="problem" class="main">
					<h1 class="title"></h1>
					<table class="data">
						<tr>
							<td>{#wordsPoints#}</td>
							<td class="points"></td>
							<td>{#arenaCommonMemoryLimit#}</td>
							<td class="memory_limit"></td>
						</tr>
						<tr>
							<td>{#arenaCommonTimeLimit#}</td>
							<td class="time_limit"></td>
							<td>{#arenaCommonOverallWallTimeLimit#}</td>
							<td class="overall_wall_time_limit"></td>
						</tr>
						<tr>
							<td>{#wordsInOut#}</td>
							<td>{#wordsConsole#}</td>
							<td>{#problemEditFormInputLimit#}</td>
							<td class="input_limit"></td>
						</tr>
					</table>
{if $admin}
					<form enctype="multipart/form-data" action="/api/problem/update" method="post" id="update-problem">
						<fieldset>
							<legend>Administrar problema</legend>
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
					<div id="qualitynomination">
						<div id="qualitynomination-popup"></div>
					</div>
{if !empty($ENABLED_EXPERIMENTS) && in_array('ephemeral', $ENABLED_EXPERIMENTS)}
					<iframe id="ephemeral-embedded-grader" src="/grader/ephemeral/?embedded"></iframe>
{/if}
{if $showPoints}
{include file='arena.runs.tpl' show_points=true show_submit=true show_details=true inline}
{else}
{include file='arena.runs.tpl' show_submit=true show_details=true inline}
{/if}
				</div>
			</div>
{if $admin}
			<div id="runs" class="tab">
{include file='arena.runs.tpl' show_pager=true show_points=true show_user=true show_problem=true show_rejudge=true show_details=true show_disqualify=true inline}
			</div>
{/if}
{if $showRanking}
			<div id="ranking" class="tab">
				<div></div>
			</div>
{/if}
{include file='arena.clarification_list.tpl' contest=true inline}
		</div>
		<div id="overlay">
{if !empty($payload)}
{include file='arena.runsubmit.tpl' payload=$payload inline}
{else}
{include file='arena.runsubmit.tpl' payload=[] inline}
{/if}
{include file='arena.clarification.tpl' admin=$admin inline}
			<div id="run-details"></div>
		</div>
{include file='common.analytics.tpl' inline}
	</body>
</html>
