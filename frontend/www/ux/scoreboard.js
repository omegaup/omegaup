$(document).ready(function() {
	
	var params = /\/arena\/([^\/]+)\/scoreboard\/([^\/]+)\/?/.exec(window.location.pathname);
	var contestAlias = params[1];
	var token = params[2];
	var isTableHeadSet = false;
	var getRankingByTokenRefresh = 5 * 60 * 1000; // 5 minutes
	var updateClockRefresh = 1000; // 1 sec
	var startTime = null;
	var submissionDeadline = null;
	
	// Update scoreboard
	omegaup.getRankingByToken(contestAlias, token, rankingChange);
	setInterval(function() { omegaup.getRankingByToken(contestAlias, token, rankingChange); }, getRankingByTokenRefresh);
	$('#ranking').show();
	
	// Update time left
	updateClock();
	setInterval(updateClock, updateClockRefresh);		
	
	function rankingChange(data) {		
		console.time("rankingChange");		
		
		// Set global start and submission deadline times out of result data
		startTime = data.start_time;
		submissionDeadline = data.submission_deadline;		
		
		$('#ranking tbody tr.inserted').remove();

		var ranking = data.ranking;
		var newRanking = {};		
		
		// Set table headings
		if (isTableHeadSet === false) {
			
			// Take problems from the first user
			var rank = ranking[0];
			var letter = 65;
			for (var alias in rank.problems) {				
				$('<th colspan="2"><a href="#problems/' + alias + '" title="' + alias + '">' + String.fromCharCode(letter++) + '</a></th>').insertBefore('#ranking thead th.total');
				$('<td class="prob_' + alias + '_points"></td>').insertBefore('#ranking tbody .template td.points');
				$('<td class="prob_' + alias + '_penalty"></td>').insertBefore('#ranking tbody .template td.points');
			}				
			
			$('#title .contest-title').html(data.title);
			
			isTableHeadSet = true;			
		}

		// Push data to ranking table
		for (var i = 0; i < ranking.length; i++) {
			var rank = ranking[i];
			newRanking[rank.username] = i;
			
			var r = $('#ranking tbody tr.template').clone().removeClass('template').addClass('inserted').addClass('rank-new')
			
			var username = rank.username +
				((rank.name == rank.username) ? '' : (' (' + omegaup.escape(rank.name) + ')'));
			$('.user', r).html(username);

			for (var alias in rank.problems) {
				if (!rank.problems.hasOwnProperty(alias)) continue;
				
				$('.prob_' + alias + '_points', r).html(rank.problems[alias].points);
				$('.prob_' + alias + '_penalty', r).html(rank.problems[alias].penalty);
			}
			
			// if rank went up, add a class
			if (parseInt($('.points', r).html()) < parseInt(rank.total.points)) {
				r.addClass('rank-up');
			}
			
			$('.points', r).html(rank.total.points);
			$('.penalty', r).html(rank.total.penalty);
			
			$('.position', r).html(rank.place);

			$('#ranking tbody').append(r);						
		}		
		
		console.timeEnd("rankingChange");	
		
		$('#root').fadeIn('slow');
		$('#loading').fadeOut('slow');
	}
	
	function updateClock() {
		
		if (startTime === null || submissionDeadline === null) {
			return;
		}
		
		var date = new Date().getTime();
		var clock = "";

		if (date < startTime.getTime()) {
			clock = "-" + formatDelta(startTime.getTime() - (date + omegaup.deltaTime));
		} else if (date > submissionDeadline.getTime()) {
			clock = "00:00:00";
		} else {
			clock = formatDelta(submissionDeadline.getTime() - (date + omegaup.deltaTime));
		}

		$('#title .clock').html(clock);
	}
	
	function formatDelta(delta) {
		var days = Math.floor(delta / (24 * 60 * 60 * 1000));
		delta -= days * (24 * 60 * 60 * 1000);
		var hours = Math.floor(delta / (60 * 60 * 1000));
		delta -= hours * (60 * 60 * 1000);
		var minutes = Math.floor(delta / (60 * 1000));
		delta -= minutes * (60 * 1000);
		var seconds = Math.floor(delta / 1000);

		var clock = "";

		if (days > 0) {
			clock += days + ":";
		}
		if (hours < 10) clock += "0";
		clock += hours + ":";
		if (minutes < 10) clock += "0";
		clock += minutes + ":";
		if (seconds < 10) clock += "0";
		clock += seconds;

		return clock;
	}
});

