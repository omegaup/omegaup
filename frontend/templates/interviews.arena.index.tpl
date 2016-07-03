{include file='arena.head.tpl' jsfile='/js/interviews.arena.contest.js?ver=6b80ee' inContest=false}

			<div id="title">
				<h1><span class="contest-title"></span><sup class="socket-status" title="WebSocket"></sup></h1>
				<div class="clock">00:00:00</div>
			</div>

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
				</div>
				<div id="summary" class="main">
					<h1 class="title"></h1>
					<p class="description"></p>
					<table>
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
					<div class="karel-js-link hide">
						<a href="/karel.js/" target="_blank">{#openInKarelJs#} <span class="glyphicon glyphicon-new-window"></span></a>
					</div>
					<div class="statement"></div>
					<hr />
					<div class="source">{#wordsSource#}: <span></span></div>
					<div class="problemsetter">{#wordsProblemsetter#}: <a></a></div>
					{include file='arena.runs.tpl' show_points=true show_submit=true show_details=true}
				</div>
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
