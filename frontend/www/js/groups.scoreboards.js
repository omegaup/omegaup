$(function() {
	
	$(".navbar #nav-groups").addClass("active");
	
	var formData = $('#form-data');
	var formName = formData.attr('data-name');
	var formPage = formData.attr('data-page');
	var scoreboardAlias = formData.attr('data-alias');
	var groupAlias = formData.attr('data-group-alias');

	if (formPage === "edit") {
		omegaup.getContests(function(contests) {							
			for (var i = 0; i < contests.results.length; i++) {
				contest = contests.results[i];							
				$('#contests').append($('<option></option>').attr('value', contest.alias).text(contest.title));
			}
		});

		$('#scoreboard-add-contest-form').submit(function() {
			omegaup.addContestToScoreboard(
				groupAlias,
				scoreboardAlias,
				$("#contests").val(),			
				function(data) {
					if(data.status === "ok") {
						OmegaUp.ui.success("Contest successfully added!");
						refreshScoreboardContests();
					} else {
						OmegaUp.ui.error(data.error || 'error');
					}
				}
			);

			return false;
		});

		refreshScoreboardContests();

		function refreshScoreboardContests() {
			omegaup.getGroupScoreboard(groupAlias, scoreboardAlias, function(gScoreboard){
				$('#scoreboard-contests').empty();

				for (var i = 0; i < gScoreboard.contests.length; i++) {
						var contest = gScoreboard.contests[i];
						$('#scoreboard-contests').append(
							$('<tr></tr>')
								.append($('<td></td>').append(
									$('<a></a>')
										.attr('href', '/arena/' + contest.alias + '/')
										.text(omegaup.escape(contest.title))
								))							
								.append($('<td><button type="button" class="close">&times;</button></td>')
									.click((function(contestAlias) {
										return function(e) {
											omegaup.removeContestFromScoreboard(groupAlias, scoreboardAlias, contestAlias, function(response) {
												if (response.status === "ok") {
													OmegaUp.ui.success("Contest successfully removed!");

													var tr = e.target.parentElement.parentElement;
													$(tr).remove();
												} else {
													OmegaUp.ui.error(response.error || 'error');
												}
											});
										};
									})(contest.alias))
								)
						);
					}
			});
		}
	} else if (formPage === "details") {
		
		omegaup.getGroupScoreboard(groupAlias, scoreboardAlias, function(scoreboard){						
			var html = "<table class=\"merged-scoreboard\"><tr><td></td><td><b>Username</b></td>";
			
			var contests = [];
			for (var alias in scoreboard["ranking"][0]["contests"]) {
				html += "<td colspan=\"2\"><b>" + alias + "</b></td>";
				contests.push(alias);
			}	
						
			html += "<td colspan=\"2\"><b>Total</b></td>";
			html += "</tr>"
			
			ranking = scoreboard["ranking"];
			var showPenalty = false;
			for (var entry in ranking) {
				if (!ranking.hasOwnProperty(entry)) continue;
				data = ranking[entry];
				showPenalty |= !!data["total"]["penalty"];
			}

			for (var entry in ranking) {
				if (!ranking.hasOwnProperty(entry)) continue;
				data = ranking[entry];
				place = parseInt(entry) + 1;
				
				html += "<tr>";
				html += "<td><strong>" + (place) + "</strong></td>";
				html += "<td><div class=\"username\">" + data["username"] + "</div>";
				if (data["username"] != data["name"]) {
					html += "<div class=\"name\">" + data["name"] + "</div></td>";
				} else {
					html += "<div class=\"name\">&nbsp;</div></td>";
				}
				
				for (var c in contests) {
					if (showPenalty) {
						html += "<td class=\"numeric\">" + data["contests"][contests[c]]["points"] + "</td>";
						html += "<td class=\"numeric\">" + data["contests"][contests[c]]["penalty"] + "</td>";
					} else {
						html += "<td class=\"numeric\" colspan=\"2\">" + data["contests"][contests[c]]["points"] + "</td>";
					}
				}
				
				if (showPenalty) {
					html += "<td class=\"numeric\">" + data["total"]["points"] + "</td>";
					html += "<td class=\"numeric\">" + data["total"]["penalty"] + "</td>";
				} else {
					html += "<td class=\"numeric\" colspan=\"2\">" + data["total"]["points"] + "</td>";
				}
				
				html += "</tr>";
			}			
	
			html += "</table>"
			
			$("#ranking").html(html);
		});
	}
});

