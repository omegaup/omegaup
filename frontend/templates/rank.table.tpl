{if !isset($page)}
	{$page = 0}
{/if}

{if !isset($length)}
	{$length = 100}
{/if}

{if !isset($is_index)}
	{$is_index = false}
{/if}

<div class=" panel panel-default" id="problems_list" >
	<div class="panel-heading">
		<h3 class="panel-title">{#rankHeaderPreCount#} {$length} {#rankHeaderPostCount#}</h3>
		{if !$is_index}
			{if $page > 0}
				<a href="{$smarty.server.PHP_SELF}?p={$page-1}">{#wordsPrevPage#}</a> |
			{/if}
			<a href="{$smarty.server.PHP_SELF}?p={$page+1}">{#wordsNextPage#}</a>
		{/if}
	</div>
	<table class="table table-striped table-hover" id="rank-by-problems-solved">
		<thead>
			<tr>
				<th>#</th>
				<th>{#wordsUser#}</th>
				<th>{#rankSolved#}</th>
			</tr>
		</thead>
		<tbody>
		</tbody>
	</table>	
	<div class="panel-body">
		{if $is_index}
		<a href='rank.php'>{#rankViewFull#}</a>
		{else}		
			{if $page > 0}
				<a href="{$smarty.server.PHP_SELF}?p={$page-1}">{#wordsPrevPage#}</a> | 
			{/if}
			<a href="{$smarty.server.PHP_SELF}?p={$page+1}">{#wordsNextPage#}</a>
		{/if}
	</div>	

	<script language="javascript">
		omegaup.getRankByProblemsSolved(
				{$length}*{$page},
				{$length},
				function(result) {
					var html = "";
					for (a = 0; a < result.rank.length; a++)
					{
						html += "<tr><td>" + result.rank[a].rank + "</td><td><b><a href=/profile/"+ result.rank[a].username + ">"
							+ ""+result.rank[a].username + "</a></b>"
							+ "<br/>" + (result.rank[a].name == null ? "&nbsp;" : result.rank[a].name) + "</td>"
							+ "<td>"+result.rank[a].problems_solved + "</td></tr>";
					}
					$("#rank-by-problems-solved>tbody").append(html);
				}
		);
	</script>
</div>
