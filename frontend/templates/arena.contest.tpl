{include file='head.tpl' jsfile=$jsfile inContest=$showNavigation inArena=true}
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
					<div id="problem-list">
						<div class="summary">
							<a class="name" href="#problems">{#wordsSummary#}</a>
						</div>
						<div class="template">
							<a class="name"></a>
							<span class="solved"></span>
						</div>
					</div>
{if $showRanking}
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
						<tr>
							<td>{#wordsInOut#}</td>
							<td>{#wordsConsole#}</td>
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
{if $showPoints}
{include file='arena.runs.tpl' show_points=true show_submit=true show_details=true}
{else}
{include file='arena.runs.tpl' show_submit=true show_details=true}
{/if}
				</div>
			</div>
{if $admin}
			<div id="runs" class="tab">
{include file='arena.runs.tpl' show_pager=true show_points=true show_user=true show_problem=true show_rejudge=true show_details=true}
			</div>
{/if}
{if $showRanking}
			<div id="ranking" class="tab">
				<div></div>
			</div>
{/if}
{include file='arena.clarification_list.tpl' contest=true}
		</div>
		<div id="overlay">
{include file='arena.runsubmit.tpl'}
{include file='arena.clarification.tpl'}
{include file='arena.rundetails.tpl'}
		</div>
	</body>
</html>
