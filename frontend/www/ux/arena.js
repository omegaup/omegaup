$(document).ready(function() {
	Date.setLocale("es");
	
	function makeWorldClockLink(date) {
		try {
			return "http://timeanddate.com/worldclock/fixedtime.html?iso=" + date.iso();
		} catch (e) {
			return '#';
		}
	}
	
	function convertSecondsToReadableTime(seconds) {
		var time = new Date(seconds);
		return time.format('{h}h {mm}m', 'es');
	}
	
	var omegaup = new OmegaUp();

	omegaup.getContests(function (data) {
		var list = data.results;
		var current = $('#current-contests');
		var past = $('#past-contests');
		var now = new Date();

		for (var i = 0, len = list.length; i < len; i++) {
			var start = list[i].start_time;
			var end = list[i].finish_time;
			((end > now) ? current : past).append(
				$('<tr>' +
					'<td><a href="/arena/' + list[i].alias + '">' + list[i].title + '</a></td>' +
					'<td>' + list[i].description + '</td>' +
					'<td class="no-wrap"><a href="' + makeWorldClockLink(start) + '">' + start.long() + '</a></td>' +
					'<td class="no-wrap"><a href="' + makeWorldClockLink(end) + '">' + end.long() + '</a></td>' + 
					'<td class="no-wrap">' + convertSecondsToReadableTime(list[i].duration) + '</td>' +
					'<td>' + (end < now ? '<a href="/arena/' + list[i].alias + '/practice/">Práctica</a>' : '') + '</td>' +
				'</tr>')
			);
		}

		$('#root').show();
		$('#loading').fadeOut('slow');
	});

	$('#contest-list tr').live('click', function() {
	});
});
