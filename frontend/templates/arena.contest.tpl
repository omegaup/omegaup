{include file='arena.head.tpl' jsfile='/ux/contest.js'}
			<div id="title">
				<h1 class="contest-title"></h1>
				<div class="clock">00:00:00</div>
			</div>
			<ul class="tabs">
				<li><a href="#problems" class="active">{#wordsProblems#}</a></li>
				<li><a href="#ranking">{#wordsRanking#}</a></li>
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
							<td class="message"></td>
							<td class="answer"></td>
						</tr>
					</tbody>
				</table>
			</div>
		</div>
		<div id="overlay">
			<form id="submit" method="POST">
				<button class="close">&times;</button>
				<div id="lang-select">
					Lenguaje
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
				{#wordsProblems#}
				<select name="problem">
				</select><br/>
				<textarea name="message"></textarea><br/>
				<input type="submit" />
			</form>
		</div>
		<div id="footer">
		</div>
	</body>
</html>
