{include file='arena.head.tpl' jsfile='/ux/contest.js?ver=bf50e4' bodyid='only-problem'}
			{if $problem_admin}
			<script type="text/javascript" src="/ux/libadmin.js?ver=5eb094"></script>
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
					<div class="source">Fuente: <span>{$source|escape}</span></div>
					<table class="runs">
						<caption>{#wordsSubmissions#}</caption>
						<thead>
							<tr>
								<th>{#wordsID#}</th>
								<th>{#wordsStatus#}</th>
								<th class="numeric">{#wordsPercentage#}</th>
								<th class="numeric">{#wordsPenalty#}</th>
								<th>{#wordsLanguage#}</th>
								<th class="numeric">{#wordsMemory#}</th>
								<th class="numeric">{#wordsRuntime#}</th>
								<th>{#wordsDetails#}</th>
							</tr>
						</thead>
						<tfoot>
							<tr>
								<td colspan="9"><a href="#problems/new-run">{#wordsNewSubmissions#}</a></td>
							</tr>
						</tfoot>
						<tbody class="run-list-template">
							<tr>
								<td class="guid"></td>
								<td class="status"></td>
								<td class="percentage numeric"></td>
								<td class="penalty numeric"></td>
								<td class="language"></td>
								<td class="memory numeric"></td>
								<td class="runtime numeric"></td>
								<td><button class="details glyphicon glyphicon-zoom-in"></button></td>
							</tr>
						</tbody>
					</table>
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
				<table class="runs">
					<caption>
						Envíos
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
							<input id="runsusername" type="text" class="typeahead form-control" autocomplete="off"/>
							<button type="button" class="close" id="runsusername-clear" style="float: none;">&times;</button>
								
						</div>
					</caption>
					<thead>
						<tr>
							<th>{#wordsTime#}</th>
							<th class="hidden-sm hidden-xs">GUID</th>
							<th>{#wordsUser#}</th>
							<th>{#wordsStatus#}</th>
							<th class="numeric">{#wordsPercentage#}</th>
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
							<td class="guid hidden-sm hidden-xs"></td>
							<td class="username"></td>
							<td class="status"></td>
							<td class="percentage numeric"></td>
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
			<form id="submit" method="POST">
				<button class="close">&times;</button>
				<div id="lang-select">
					{#wordsLanguage#}
					<select name="language">
						<option value=""></option>
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
				</div>
				<textarea name="code"></textarea><br/>
				<input type="file" id="code_file" /><br/>
				<input type="submit" />
			</form>
{include file='arena.rundetails.tpl'}
		</div>
		<div id="footer">
		</div>
	</body>
</html>
