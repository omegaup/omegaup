<div class="wait_for_ajax panel panel-default" id="problems_list" >
	<div class="panel-heading">
		<h3 class="panel-title">{#rankHeaderPreCount#} {$count} {#rankHeaderPostCount#}</h3>
	</div>
	<table class="table table-striped table-hover" id="rank-by-problems-solved">
		<thead>
			<tr>
				<th>{#rankUser#}</th>				
				<th>{#rankSolved#}</th>
			</tr>
		</thead>
		<tbody>
			{foreach from=$rank.rank item=data}
				<tr>
					<td><b><a href='/profile/{$data.username}'>{$data.username}</a></b></td>
					<td>{$data.problems_solved}</td>
				</tr>
			{/foreach}
		</tbody>
	</table>
	<div class="panel-body">
		<a href='rank.php'>{#rankViewFull#}</a>
	</div>	
</div>
