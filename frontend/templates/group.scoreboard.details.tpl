{include file='redirect.tpl'}
{include file='arena.head.tpl' jsfile='/js/groups.scoreboards.js'}

<span id="form-data" data-name="group-scoreboards" data-page="details" data-alias="{$smarty.get.scoreboard}" data-group-alias="{$smarty.get.group}">
</span>


	
		<div id="title">
			<h1><span class="scoreboard-title" id="scoreboard-title"></span></h1>			
		</div>
		<div id="ranking">			
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
					</tr>
				</tbody>
			</table>
		</div>
	
</div>			
</body>
</html>
