{include file='arena.head.tpl' jsfile='/ux/admin.js'}
			<audio id="notification_audio">
				<source src="/media/notification.mp3" type="audio/mpeg" />
			</audio>
			<div id="title">
				<h1 class="contest-title"></h1>
				<div class="clock">0:00:00</div>
			</div>
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
						
						<label for="runsstatus">Status: </label>
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
							<option value="java">Java</option>
							<option value="py">Python</option>
							<option value="rb">Ruby</option>
							<option value="pl">Perl</option>
							<option value="cs">C#</option>
							<option value="p">Pascal</option>
							<option value="kp">Karel (Pascal)</option>
							<option value="kj">Karel (Java)</option>
						</select>
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
						<th>Status</th>
						<th>{#wordsPoints#}</th>
						<th>{#wordsPenalty#}</th>
						<th>Rejuecear</th>
						<th>{#wordsDetails#}</th>
					</tr>
				</thead>
				<tfoot>
					<tr>
						<td colspan="13"><a href="#new-run">Nuevo envío</a></td>
					</tr>
				</tfoot>
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
			<div id="ranking-chart"></div>
			<table id="ranking" style="display: block;">
				<thead>
					<tr>
						<th></th>
						<th></th>
						<th>{#wordsUser#}</th>
						<th class="total" colspan="2">Total</th>
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
			<table class="clarifications">
				<caption>
					Clarificaciones 
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
						<td colspan="6"><a href="#clarifications/new">Nueva clarificación</a></td>
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
			<form enctype="multipart/form-data" action="/api/problem/update" method="post" id="update-problem">
				<fieldset>
					<legend>Administrar problema <select id="rejudge-problem-list" name="problem_alias"></select></legend>
					<button id="rejudge-problem" value="Rejuecear">Rejuecear</button>
					<input name="problem_contents" type="file" />
					<button id="upload-problem" type="submit">Actualizar casos/redacci&oacute;n</button>
				</fieldset>
			</form>
		</div>
		<div id="overlay">
			<form id="submit" method="POST">
				<button class="close">&times;</button>
				{#wordsProblem#}
				<select name="problem"></select>
				{#wordsLanguage#}
				<select name="language">
					<option value="c">C</option>
					<option value="cpp">C++</option>
					<option value="p">Pascal</option>
				</select><br/>
				<textarea name="code"></textarea><br/>
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
