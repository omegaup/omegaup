{include file='arena.head.tpl' jsfile='/ux/contest.js' bodyid='practice'}
			<div id="title">
				<h1 class="contest-title"></h1>
				<div class="clock" style="font-size: 5em; line-height: .4em; margin-bottom: .2em;">&infin;</div>
			</div>
			<div id="problems" class="tab navleft">
				<div class="navbar">
					<div id="problem-list">
						<div class="summary">
							<a class="name" href="#problems">Resumen</a>
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
						<tr><td><strong>Hora de inicio</strong></td><td class="start_time"></td></tr>
						<tr><td><strong>Hora de fin</strong></td><td class="finish_time"></td></tr>
						<tr><td><strong>Tiempo para resolver los problemas</strong></td><td><span class="window_length"></span> minutos</td></tr>
					</table>
				</div>
				<div id="problem" class="main">
					<h1 class="title"></h1>
					<table class="data">
						<tr>
							<td>Puntos</td>
							<td class="points"></div>
							<td>Validador</td>
							<td class="validator"></div>
						</tr>
						<tr>
							<td>Límite de tiempo</td>
							<td class="time_limit"></td>
							<td>Límite de memoria</td>
							<td class="memory_limit"></td>
						</tr>
					</table>
					<div class="statement"></div>
					<hr />
					<div class="source">Fuente: <span></span></div>
					<table class="runs">
						<caption>Envíos</caption>
						<thead>
							<tr>
								<th>ID</th>
								<th>Lenguaje</th>
								<th>Tiempo de Ejecución</th>
								<th>Memoria</th>
								<th>Tiempo</th>
								<th>Status</th>
								<th>Puntos</th>
								<th>Penalty</th>
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
		</div>
		<div id="overlay">
			<form id="submit" method="POST">
				<button class="close">&times;</button>
				<div id="lang-select">
					Lenguaje
					<select name="language">
						<option value=""></option>
						<option value="cpp">C++</option>
						<option value="c">C</option>
						<option value="java">Java</option>
						<option value="p">Pascal</option>
						<option value="cat">Solo Salida</option>
						<option value="kp">Karel (Pascal)</option>
						<option value="kj">Karel (Java)</option>
					</select>
				</div>
				<textarea name="code"></textarea><br/>
				<input type="submit" />
			</form>			
		</div>
		<div id="footer">
		</div>
	</body>
</html>
