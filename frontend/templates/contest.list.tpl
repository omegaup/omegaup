<div class="wait_for_ajax panel panel-default no-bottom-margin" id="contest_list">
	
	<div class="bottom-margin">
			{#forSelectedItems#}: 
			<div class="btn-group">
				<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
				  {#selectAction#}<span class="caret"></span>
				</button>
				<ul class="dropdown-menu" role="menu">
				  <li><a id="bulk-make-public">{#makePublic#}</a></li>
				  <li><a id="bulk-make-private">{#makePrivate#}</a></li>				  
				  <li class="divider"></li>				  
				</ul>
			  </div>
	</div>
				  
	<div class="panel-heading">
		<h3 class="panel-title">{#wordsContests#}</h3>
	</div>
	
	<table class="table">
		<thead>
			<th></th>
			<th>{#contestsTableTitle#}</th>
			<th>{#contestsTableStart#}</th>
			<th>{#contestsTableEnd#}</th>
			<th>{#contestsTablePublic#}</th>
			<th></th>
			<th></th>
			<th></th>
			<th></th>
			<th></th>
		</thead>
		<tbody>
		</tbody>
	</table>
</div>
<script>
	(function(){
		function makeWorldClockLink(date) {
			try {
				return "http://timeanddate.com/worldclock/fixedtime.html?iso=" + date.toISOString();
			} catch (e) {
				return '#';
			}
		}
	
		function fillContestsTable() {
			omegaup.getMyContests(function(contests) {
				// Got the contests, lets draw them

				var html = "";

				for (var i = 0; i < contests.results.length; i++) {
					var startDate = contests.results[i].start_time;
					var endDate = contests.results[i].finish_time;
					html += "<tr>"
						+ "<td><input type='checkbox' id='" + contests.results[i].alias + "'/></td>" 
						+ "<td><b><a href='/arena/" + contests.results[i].alias  + "/'>" + omegaup.escape(contests.results[i].title) + "</a></b></td>"					
						+ '<td><a href="' + makeWorldClockLink(startDate) + '">' + startDate.format("long", "es") + "</a></td>"
						+ '<td><a href="' + makeWorldClockLink(endDate) + '">' + endDate.format("long", "es") + "</a></td>"
						+ '<td>'+ ((contests.results[i].public == '1') ? 'Sí' : 'No')  + '</td>'
						+ '<td><a href="/contestedit.php?contest=' + contests.results[i].alias  + '">{#wordsEdit#}</a></td>'
						+ '<td><a href="/addproblemtocontest.php?contest=' + contests.results[i].alias  + '">Agregar problemas</a></td>'
						+ '<td><a href="/addusertoprivatecontest.php?contest=' + contests.results[i].alias  + '">{#contestListAddContestants#}</a></td>'
						+ "<td><a href='/arena/" + contests.results[i].alias  + "/admin/'>Envíos</a></td>"
						+ '<td><a href="/conteststats.php?contest=' + contests.results[i].alias  + '">Estadísticas</a></td>'
						+ "</tr>";
				}

				$("#contest_list").removeClass("wait_for_ajax");
				$("#contest_list > table > tbody").empty().html(html);
			});
		}
		fillContestsTable();
		
		$("#bulk-make-public").click(function() {
			OmegaUp.ui.bulkOperation(
				function(alias, handleResponseCallback) {
					omegaup.updateContest(
							alias, 
							null,
							null,
							null,
							null,
							null,
							null,
							null,					 
							null,
							null, 
							null,
							1 /*public*/,
							null, 
							null, 					
							null,
							handleResponseCallback);					
				},
				function() {
					fillContestsTable();
				}
			)}
		);
		
		$("#bulk-make-private").click(function() {
			OmegaUp.ui.bulkOperation(
				function(alias, handleResponseCallback) {
					omegaup.updateContest(
							alias, 
							null,
							null,
							null,
							null,
							null,
							null,
							null,					 
							null,
							null, 
							null,
							0 /*public*/,
							null, 
							null, 					
							null,
							handleResponseCallback);
				},
				function() {
					fillContestsTable();
				}
			)}
		);
	})();
</script>

