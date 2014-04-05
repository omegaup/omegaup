<div class="wait_for_ajax panel panel-default" id="problems_list" >
	<div class="panel-heading">
		<h3 class="panel-title">{#rankHeaderPreCount#} {$rank.rank|@count} {#rankHeaderPostCount#}</h3>
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
				30*{$page},
				30,
				function(result) {
					var html = "";
					for (a = 0; a < result.rank.length; a++)
					{
						html += "<tr><td>"+result.rank[a].name + ""
							+ " ("+result.rank[a].username + ")</td>"
							+ "<td>"+result.rank[a].problems_solved + "</td></tr>";
					}
					$("#rank-by-problems-solved>tbody").append(html);
				}
		);
	</script>
</div>
