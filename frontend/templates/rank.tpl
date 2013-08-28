{include file='head.tpl'}
{include file='mainmenu.tpl'}

<div class="wait_for_ajax panel panel-default" id="problems_list" >
	<div class="panel-heading">
		<h3 class="panel-title">Top 100 usuarios con más problemas resueltos</h3>
	</div>
	<table class="table" id="rank-by-problems-solved">
		<thead>
			<tr>
				<th>Username</th>				
				<th>Problemas resueltos</th>
			</tr>
		</thead>
		<tbody></tbody>
	</table>
</div>

<script>

	omegaup.getRankByProblemsSolved(function(data) {
		var rank = data["rank"];
		for (var i in rank) {
			var username = rank[i].username;
			var name = rank[i].name;
			
			var content = "<tr><td>" + username + "</td><td>" + rank[i].problems_solved + "</td></tr>"; 
			
			$('#rank-by-problems-solved tbody').append(content);
		}
	});

</script>

<script>
	$(".navbar #nav-rank").addClass("active");
</script>

{include file='footer.tpl'}