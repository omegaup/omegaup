$(document).ready(function() {
	var arena = new Arena();
	var params = /\/arena\/([^\/]+)\/scoreboard\/([^\/]+)\/?/.exec(window.location.pathname);
	var contestAlias = params[1];
	var token = params[2];
	var getRankingByTokenRefresh = 5 * 60 * 1000; // 5 minutes
	
	// Update scoreboard
	omegaup.getContestByToken(contestAlias, token, function(contest) {
		arena.initProblems(contest);
		arena.initClock(contest.start_time, contest.finish_time);
		$('#title .contest-title').html(contest.title);

		omegaup.getRankingByToken(contestAlias, token, arena.onRankingChanged.bind(arena));
		if (new Date() < contest.finish_time) {
			setInterval(function() {
				omegaup.getRankingByToken(contestAlias, token, arena.onRankingChanged.bind(arena));
			}, getRankingByTokenRefresh);
		}

		$('#ranking').show();
		$('#root').fadeIn('slow');
		$('#loading').fadeOut('slow');
	});
});
