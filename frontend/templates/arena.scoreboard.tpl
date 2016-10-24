{include file='arena.head.tpl' jsfile={version_hash src='/ux/scoreboard.js'}}
		<div id="scoreboard">
			<div id="title">
				<h1><span class="contest-title"></span><sup class="socket-status" title="WebSocket"></sup></h1>
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
					<tbody class="user-list-template">
						<tr>
							<td class="position"></td>
							<td class="legend"></td>
							<td class="user"></td>
							<td class="points"></td>
						</tr>
					</tbody>
				</table>
			</div>
		</div>
	</div>
</body>
</html>
