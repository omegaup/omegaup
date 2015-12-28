{include file='arena.head.tpl' jsfile=$jsfile inContest=!$practice}

{if $admin}
			<script type="text/javascript" src="/ux/libadmin.js?ver=a96398"></script>
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
					<table class="runs">
						<caption>{#wordsSubmissions#}</caption>
						<thead>
							<tr>
								<th>{#wordsTime#}</th>
								<th>{#wordsID#}</th>
								<th>{#wordsStatus#}</th>
{if $practice}
								<th class="numeric">{#wordsPercentage#}</th>
{else}
								<th class="numeric">{#wordsPoints#}</th>
{/if}
								<th class="numeric">{#wordsPenalty#}</th>
								<th>{#wordsLanguage#}</th>
								<th class="numeric">{#wordsMemory#}</th>
								<th class="numeric">{#wordsRuntime#}</th>
								<th>{#wordsDetails#}</th>
							</tr>
						</thead>
						<tfoot>
							<tr>
								<td colspan="9"><a href="#problems/run">{#wordsNewSubmissions#}</a></td>
							</tr>
						</tfoot>
						<tbody class="run-list-template">
							<tr>
								<td class="time"></td>
								<td class="guid"></td>
								<td class="status"></td>
{if $practice}
								<td class="percentage numeric"></td>
{else}
								<td class="points numeric"></td>
{/if}
								<td class="penalty numeric"></td>
								<td class="language"></td>
								<td class="memory numeric"></td>
								<td class="runtime numeric"></td>
								<td><button class="details glyphicon glyphicon-zoom-in"></button></td>
							</tr>
						</tbody>
					</table>
				</div>
			</div>
{if $admin}
			<div id="runs" class="tab">
				<table class="runs">
					<caption>
						{#wordsSubmissions#}
						<div class="runspager">
							<button class="runspagerprev">&lt;</button>
							<button class="runspagernext">&gt;</button>

							<label for="runsverdict">{#wordsVerdict#}: </label>
							<select class="runsverdict" name="runsverdict">
								<option value="">{#wordsAll#}</option>
								<option value="AC">AC</option>
								<option value="PA">PA</option>
								<option value="WA">WA</option>
								<option value="TLE">TLE</option>
								<option value="MLE">MLE</option>
								<option value="OLE">OLE</option>
								<option value="RTE">RTE</option>
								<option value="RFE">RFE</option>
								<option value="CE">CE</option>
								<option value="JE">JE</option>
								<option value="NO-AC">No AC</option>
							</select>

							<label for="runsstatus">{#wordsStatus#}: </label>
							<select class="runsstatus" name="runsstatus">
								<option value="">{#wordsAll#}</option>
								<option value="new">new</option>
								<option value="waiting">waiting</option>
								<option value="compiling">compiling</option>
								<option value="running">running</option>
								<option value="ready">ready</option>
							</select>

							<label for="runsproblem">{#wordsProblem#}: </label>
							<select class="runsproblem">
								<option value="">{#wordsAll#}</option>
							</select>

							<label for="runslang">{#wordsLanguage#}: </label>
							<select class="runslang" name="runslang">
								<option value="">{#wordsAll#}</option>
								<option value="cpp11">C++11</option>
								<option value="cpp">C++</option>
								<option value="c">C</option>
								<option value="hs">Haskell</option>
								<option value="java">Java</option>
								<option value="pas">Pascal</option>
								<option value="py">Python</option>
								<option value="rb">Ruby</option>
								<option value="kp">Karel (Pascal)</option>
								<option value="kj">Karel (Java)</option>
								<option value="cat">{#wordsJustOutput#}</option>
							</select>

							<label for="runsusername">Usuario: </label>
							<input id="runsusername" type="text"   class="typeahead form-control" autocomplete="off"/>
							<button type="button" class="close" id="runsusername-clear" style="float: none;">&times;</button>

						</div>
					</caption>
					<thead>
						<tr>
							<th>{#wordsTime#}</th>
							<th class="numeric">Id</th>
							<th>GUID</th>
							<th>{#wordsUser#}</th>
							<th>{#wordsProblem#}</th>
							<th>{#wordsStatus#}</th>
							<th class="numeric">{#wordsPoints#}</th>
							<th class="numeric">{#wordsPenalty#}</th>
							<th>{#wordsLanguage#}</th>
							<th class="numeric">{#wordsMemory#}</th>
							<th class="numeric">{#wordsRuntime#}</th>
							<th>{#wordsRejudge#}</th>
							<th>{#wordsDetails#}</th>
						</tr>
					</thead>
					<tbody class="run-list-template">
						<tr>
							<td class="time"></td>
							<td class="id numeric"></td>
							<td class="guid"></td>
							<td class="username"></td>
							<td class="problem"></td>
							<td class="status"></td>
							<td class="points numeric"></td>
							<td class="penalty numeric"></td>
							<td class="language"></td>
							<td class="memory numeric"></td>
							<td class="runtime numeric"></td>
							<td class="rejudge"></td>
							<td><button class="admin-details glyphicon glyphicon-zoom-in"></button></td>
						</tr>
					</tbody>
				</table>
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
				<textarea name="message"></textarea><br/>
				<input type="submit" />
			</form>
{include file='arena.rundetails.tpl'}
		</div>
	</body>
</html>
