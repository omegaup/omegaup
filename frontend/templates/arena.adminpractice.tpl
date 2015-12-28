{include file='arena.head.tpl' jsfile='/ux/admin.js?ver=b36af2'}
			<script type="text/javascript" src="/ux/libadmin.js?ver=a96398"></script>
			<div id="title">
				<h1 class="contest-title">Envíos globales</h1>
			</div>
			<div id="runs">
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

							<label for="runsproblem">Problema: </label>
							<input id="runsproblem" type="text" class="typeahead form-control" autocomplete="off" />
							<button type="button" class="close" id="runsproblem-clear" style="float: none;">&times;</button>

							<label for="runslang">Lenguaje: </label>
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
							<th class="numeric">Id</th>
							<th>GUID</th>
							<th>{#wordsUser#}</th>
							<th>{#wordsProblem#}</th>
							<th>{#wordsStatus#}</th>
							<th class="numeric">{#wordsPoints#}</th>
							<th class="numeric">{#wordsPercentage#}</th>
							<th class="numeric">{#wordsPenalty#}</th>
							<th>{#wordsLanguage#}</th>
							<th class="numeric">{#wordsMemory#}</th>
							<th class="numeric">{#wordsRuntime#}</th>
							<th>Rejuecear</th>
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
		</div>
		<div id="overlay">
{include file='arena.rundetails.tpl'}
		</div>
	</body>
</html>
