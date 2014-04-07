<div class=" panel panel-default" id="problems_list" >
	<div class="panel-heading">
		<h3 class="panel-title">{#rankHeaderPreCount#} {#rankHeaderPostCount#}</h3>
		{if $page > 0}
			<a href="{$smarty.server.PHP_SELF}?p={$page-1}">{#wordsPrevPage#}</a>
		{/if}
		<a href="{$smarty.server.PHP_SELF}?p={$page+1}">{#wordsNextPage#}</a>
	</div>
	<table class="table table-striped table-hover" id="rank-by-problems-solved">
		<thead>
			<tr>
				<th>{#wordsUser#}</th>
				<th>{#rankSolved#}</th>
			</tr>
		</thead>
		<tbody>
		</tbody>
	</table>
	<script language="javascript">
		omegaup.getRankByProblemsSolved(
				100*{$page},
				100,
				function(result) {
					var html = "";
					for (a = 0; a < result.rank.length; a++)
					{
						html += "<tr><td><b><a href=\""+result.rank[a].username + "\" >"
							+ ""+result.rank[a].username + "</a></b>"
							+ " "+ (result.rank[a].name == null ? "" : result.rank[a].name) + "</td>"
							+ "<td>"+result.rank[a].problems_solved + "</td></tr>";
					}
					$("#rank-by-problems-solved>tbody").append(html);
				}
		);
	</script>
</div>
