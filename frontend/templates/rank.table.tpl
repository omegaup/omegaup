{assign var='count' value=$count|default:100}

<div class="wait_for_ajax panel panel-default" id="problems_list" >
	<div class="panel-heading">
		<h3 class="panel-title">Top {$count} usuarios con más problemas resueltos</h3>
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
	<div id="rank-wait"><img src="/media/wait.gif" /></div>
</div>

<script>

	omegaup.getRankByProblemsSolved({$count}, function(data) {
		$('#rank-wait').hide();
		
		var rank = data["rank"];
		for (var i in rank) {
			var username = rank[i].username;
			var name = rank[i].name;
			
			var content = "<tr><td><b><a href='/profile/" + username + "'>" + username + "</a></b></td><td>" + rank[i].problems_solved + "</td></tr>"; 
			
			$('#rank-by-problems-solved tbody').append(content);
		}
	});

</script>