$(document).ready(function() {
	Date.setLocale(OmegaUp.T.locale);

	function makeWorldClockLink(date) {
		try {
			return "http://timeanddate.com/worldclock/fixedtime.html?iso=" + date.iso();
		} catch (e) {
			return '#';
		}
	}

	var omegaup = new OmegaUp();

	omegaup.getContests(function (data) {
		var list = data.results;
		var current = $('#current-contests');
		var recommendedCurrent = $('#recommended-current-contests');
		var past = $('#past-contests');
		var recommendedPast = $('#recommended-past-contests');
		var now = new Date();

		for (var i = 0, len = list.length; i < len; i++) {
			var start = list[i].start_time;
			var end = list[i].finish_time;

			var target = null;

			if (list[i].recommended === '0') {
				target = (end > now) ? current : past;
			} else {
				target = (end > now) ? recommendedCurrent : recommendedPast;
			}

			target.append(
					$('<tr>' +
						'<td><a href="/arena/' + list[i].alias + '">' + omegaup.escape(list[i].title) + (list[i].recommended === '0' ? '' : ' <span class="glyphicon glyphicon-ok" aria-hidden="true"></span>')  + '</a></td>' +
						'<td class="forcebreaks forcebreaks-arena">' + omegaup.escape(list[i].description) + '</td>' +
						(end > now ?
							'<td class="no-wrap"><a href="' + makeWorldClockLink(start) + '">' + start.long() + '</a></td>' +
							'<td class="no-wrap"><a href="' + makeWorldClockLink(end) + '">' + end.long() + '</a></td>' +
							'<td class="no-wrap">' + toHHMM(list[i].duration) + '</td>' : ''
						) +
						'<td>' + (end < now ? '<a href="/arena/' + list[i].alias + '/practice/">' + OmegaUp.T.wordsPractice + '</a>' : '') + '</td>' +
					'</tr>')
			);
		}

		$('#root').show();
		$('#loading').fadeOut('slow');
	});
});
