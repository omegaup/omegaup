$(document).ready(function() {
	var arena = new Arena();
	var params = /\/arena\/([^\/]+)\/scoreboard\/([^\/]+)\/?/.exec(window.location.pathname);
	arena.contestAlias = params[1];
	arena.scoreboardToken = params[2];
	var getRankingByTokenRefresh = 5 * 60 * 1000; // 5 minutes
	
	arena.connectSocket();
	omegaup.getContestByToken(arena.contestAlias, arena.scoreboardToken, function(contest) {
		arena.initProblems(contest);
		arena.initClock(contest.start_time, contest.finish_time);
		$('#title .contest-title').html(contest.title);

		omegaup.getRankingByToken(
			arena.contestAlias,
			arena.scoreboardToken,
			arena.rankingChange.bind(arena)
		);
		if (new Date() < contest.finish_time && !arena.socket) {
			setInterval(function() {
				omegaup.getRankingByToken(
					arena.contestAlias,
					arena.scoreboardToken,
					arena.rankingChange.bind(arena)
				);
			}, getRankingByTokenRefresh);
		}

		$('#ranking').show();
		$('#root').fadeIn('slow');
		$('#loading').fadeOut('slow');
	});
});
