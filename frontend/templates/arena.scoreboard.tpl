{include file='arena.head.tpl' jsfile='/ux/scoreboard.js'}
		<div id="title">
			<h1 class="contest-title"></h1>
			<div class="clock">00:00:00</div>
		</div>		
		<div id="ranking">			
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
	</div>			
</body>
</html>
