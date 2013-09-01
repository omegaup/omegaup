{assign var='count' value=$count|default:100}

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
		<tbody></tbody>
	</table>
	<div class="panel-body">
		<a href='rank.php'>{#rankViewFull#}</a>
	</div>
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