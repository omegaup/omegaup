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
				$('#only-ac').val(),
				$('#weight').val(),
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
								.append($('<td></td>').append(contest.only_ac ? OmegaUp.T.wordsYes : OmegaUp.T.wordsNo))
								.append($('<td></td>').append(contest.weight))
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
			var ranking = scoreboard["ranking"];
			$("#scoreboard-title").html(scoreboard.scoreboard.name);

			// Adding contest's column
			for (var c = 0; c < scoreboard.contests.length; c++) {
				var alias = scoreboard.contests[c].alias;

				$('<th><a href="/arena/' + alias + '" title="' + alias + '">' +
					c + '</a></th>').insertBefore('#ranking-table thead th.total');

				$('<td class="prob_' + alias + '_points"></td>')
					.insertBefore('#ranking-table tbody.user-list-template td.points');

				$('#ranking-table thead th').attr('colspan', '');
				$('#ranking-table tbody.user-list-template .penalty').remove();
			}

			// Adding scoreboard data:
			// Cleaning up table
			$('#ranking-table tbody.inserted').remove();

			// For each user
			for (var i = 0; i < ranking.length; i++) {
				var rank = ranking[i];

				var r = $('#ranking-table tbody.user-list-template')
					.clone()
					.removeClass('user-list-template')
					.addClass('inserted')
					.addClass('rank-new');

				var username = rank.username +
					((rank.name == rank.username) ? '' : (' (' + omegaup.escape(rank.name) + ')'));
				$('.user', r).html(username);

				// For each contest in the scoreboard
				for (var c = 0; c < scoreboard.contests.length; c++) {
					var alias = scoreboard.contests[c].alias;
					var contestResults = rank.contests[alias];

					var pointsCell = $('.prob_' + alias + '_points', r);
					pointsCell.html(
						'<div class="points">' + (contestResults.points ? '+' + contestResults.points : '0') + '</div>\n' +
						'<div class="penalty">' + contestResults.penalty + '</div>'
					);

					pointsCell.removeClass('pending accepted wrong');
				}

				$('td.points', r).html(
					'<div class="points">' + rank.total.points + '</div>' +
					'<div class="penalty">' + rank.total.penalty + '</div>'
				);
				$('.position', r)
					.html(i + 1)
					.removeClass('recent-event');

				$('#ranking-table').append(r);
			}

			$('#ranking').show();
			$('#root').fadeIn('slow');
			$('#loading').fadeOut('slow');
		});
	}
});

