{include file='arena.head.tpl' jsfile='/ux/admin.js'}
			<audio id="notification_audio">
				<source src="/media/notification.mp3" type="audio/mpeg" />
			</audio>
			<div id="title">
				<h1 class="contest-title"></h1>
				<div class="clock">00:00:00</div>
			</div>
			<ul class="tabs">
				<li><a href="#problems" class="active">{#wordsProblems#}</a></li>
				<li><a href="#ranking">{#wordsRanking#}</a></li>
				<li><a href="#runs">{#wordsRuns#}</a></li>
				<li><a href="#clarifications">{#wordsClarifications#}<span id="clarifications-count"></span></a></li>
			</ul>
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
					<table id="mini-ranking">
						<thead>
							<tr>
								<th></th>
								<th>{#wordsUser#}</th>
								<th class="total" colspan="2">{#wordsTotal#}</th>
							</tr>
						</thead>
						<tbody>
							<tr class="template">
								<td class="position"></td>
								<td class="user"></td>
								<td class="points"></td>
								<td class="penalty"></td>
							</tr>
						</tbody>
					</table>
				</div>
				<div id="summary" class="main">
					<h1 class="title"></h1>
					<p class="description"></p>
					<table>
						<tr><td><strong>{#arenaPracticeStartTime#}</strong></td><td class="start_time"></td></tr>
						<tr><td><strong>{#arenaPracticeEndtime#}</strong></td><td class="finish_time"></td></tr>
						<tr><td><strong>Tiempo para resolver los problemas</strong></td><td> {#wordsMinutes#}</td></tr>
					</table>
				</div>
				<div id="problem" class="main">
					<h1 class="title"></h1>
					<table class="data">
						<tr>
							<td>{#wordsPoints#}</td>
							<td class="points"></div>
							<td>{#wordsValidator#}</td>
							<td class="validator"></div>
						</tr>
						<tr>
							<td>{#arenaCommonTimeLimit#}</td>
							<td class="time_limit"></td>
							<td>{#arenaCommonMemoryLimit#}</td>
							<td class="memory_limit"></td>
						</tr>
					</table>
					<form enctype="multipart/form-data" action="/api/problem/update" method="post" id="update-problem">
						<fieldset>
							<legend>Administrar problema</legend>
							<button id="rejudge-problem" value="Rejuecear">Rejuecear</button>
							<input name="problem_alias" type="hidden" />
							<input name="problem_contents" type="file" />
							<button type="submit">Actualizar casos/redacci&oacute;n</button>
						</fieldset>
					</form>
					<div class="statement"></div>
					<hr />
					<div class="source">Fuente: <span></span></div>
					<table class="runs">
						<caption>{#wordsSubmissions#}</caption>
						<thead>
							<tr>
								<th>{#wordsID#}</th>
								<th>{#wordsLanguage#}</th>
								<th>{#wordsRuntime#}</th>
								<th>{#wordsMemoria#}</th>
								<th>{#wordsTime#}</th>
								<th>{#wordsStatus#}</th>
								<th>{#wordsPoints#}</th>
								<th>{#wordsPenalty#}</th>
								<th>C&oacute;digo</th>
							</tr>
						</thead>
						<tfoot>
							<tr>
								<td colspan="9"><a href="#problems/run">Nuevo envío</a></td>
							</tr>
						</tfoot>
						<tbody class="run-list">
							<tr class="template">
								<td class="guid"></td>
								<td class="language"></td>
								<td class="runtime"></td>
								<td class="memory"></td>
								<td class="time"></td>
								<td class="status"></td>
								<td class="points"></td>
								<td class="penalty"></td>
								<td class="code"></td>
							</tr>
						</tbody>
					</table>
				</div>
			</div>
			<div id="runs" class="tab">
				<table class="runs">
					<caption>
						Envíos 
						<div class="runspager">
							<button class="runspagerprev">&lt;</button>
							<button class="runspagernext">&gt;</button>
							
							<label for="runsveredict">Veredicto: </label>
							<select class="runsveredict" name="runsveredict">
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
							<select class="runsproblem" name="runsproblem">
								<option value="">{#wordsAll#}</option>
							</select>
							
							<label for="runslang">{#wordsLanguage#}: </label>
							<select class="runslang" name="runslang">
								<option value="">{#wordsAll#}</option>
								<option value="c">C</option>
								<option value="cpp">C++</option>
								<option value="cpp11">C++11</option>
								<option value="java">Java</option>
								<option value="py">Python</option>
								<option value="rb">Ruby</option>
								<option value="pl">Perl</option>
								<option value="cs">C#</option>
								<option value="p">Pascal</option>
								<option value="kp">Karel (Pascal)</option>
								<option value="kj">Karel (Java)</option>
							</select>
								
							<label for="runsusername">Usuario: </label>
							<input id="runsusername" type="text"  size='20'/>
							<button type="button" class="close" id="runsusername-clear" style="float: none;">&times;</button>
								
						</div>
					</caption>
					<thead>
						<tr>
							<th>Id</th>
							<th>GUID</th>
							<th>{#wordsUser#}</th>
							<th>{#wordsProblem#}</th>
							<th>{#wordsLanguage#}</th>
							<th>{#wordsRuntime#}</th>
							<th>{#wordsMemoria#}</th>
							<th>{#wordsTime#}</th>
							<th>{#wordsStatus#}</th>
							<th>{#wordsPoints#}</th>
							<th>{#wordsPenalty#}</th>
							<th>Rejuecear</th>
							<th>{#wordsDetails#}</th>
						</tr>
					</thead>
					<tbody class="run-list">
						<tr class="template">
							<td class="id"></td>
							<td class="guid"></td>
							<td class="username"></td>
							<td class="problem"></td>
							<td class="language"></td>
							<td class="runtime"></td>
							<td class="memory"></td>
							<td class="time"></td>
							<td class="status"></td>
							<td class="points"></td>
							<td class="penalty"></td>
							<td class="rejudge"></td>
							<td class="details"></td>
						</tr>
					</tbody>
				</table>
			</div>
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
					<tbody>
						<tr class="template">
							<td class="position"></td>
							<td class="legend"></td>
							<td class="user"></td>
							<td class="points"></td>
							<td class="penalty"></td>
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
							<td class="answer"><pre></pre></td>
						</tr>
					</tbody>
				</table>
			</div>
		</div>
		<div id="overlay">
			<form id="submit" method="POST">
				<button class="close">&times;</button>
				<div id="lang-select">
					{#wordsLanguage#}
					<select name="language">
						<option value=""></option>
						<option value="cpp">C++</option>
						<option value="cpp11">C++11</option>
						<option value="c">C</option>
						<option value="java">Java</option>
						<option value="p">Pascal</option>
						<option value="cat">{#wordsJustOutput#}</option>
						<option value="kp">Karel (Pascal)</option>
						<option value="kj">Karel (Java)</option>
						<option value="hs">Haskell</option>
					</select>
				</div>
				Pega el código de tu programa:
				<textarea name="code"></textarea><br/>
				O alternativamente súbelo:
				<input type="file" id="code_file" /><br/>
				<input type="submit" />
			</form>			
			<form id="clarification" method="POST">
				<button class="close">&times;</button>
				{#wordsProblem#}
				<select name="problem">
				</select><br/>
				<textarea name="message"></textarea><br/>
				<input type="submit" />
			</form>
			<form id="run-details">
				<button class="close">&times;</button>
				
				<pre class="source"></pre>
				<pre class="compile_error"></pre>
				<div class="download"><a href="#">Bajar salida</a></div>
				<div class="cases"></div>
			</form>
		</div>
	</body>
</html>
