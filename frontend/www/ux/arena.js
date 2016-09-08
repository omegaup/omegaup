$(document).ready(function() {
	Date.setLocale(OmegaUp.T.locale);

	function makeWorldClockLink(date) {
		try {
			return "http://timeanddate.com/worldclock/fixedtime.html?iso=" + date.iso();
		} catch (e) {
			return '#';
		}
	}

    // Render time in a table cell.
    function renderTime(time) {
        return '<td class="no-wrap"><a href="' +
            makeWorldClockLink(time) + '">' + time.long() + '</a></td>';
    }

    // Render contest start and end time, if necessary.
    function renderTimes(contest, output_times) {
        return !output_times
            ? ''
            : renderTime(contest.start_time) + renderTime(contest.finish_time) +
              '<td class="no-wrap">' + toHHMM(contest.duration) + '</td>';
    }

    // Render recommended icon.
    function renderRecommended(contest) {
        return (contest.recommended === '0')
            ? ''
            : ' <span class="glyphicon glyphicon-ok" aria-hidden="true"></span>';
    }

    function populateContestList(target, list, output_times) {
		var now = new Date();
		for (var i = 0, len = list.length; i < len; i++) {
            var contest = list[i];
			target.append($(
                '<tr>' +
                '<td><a href="/arena/' + contest.alias + '">' +
                omegaup.escape(contest.title) + renderRecommended(contest)  + '</a></td>' +
                '<td class="forcebreaks forcebreaks-arena">' +
                omegaup.escape(contest.description) + '</td>' +
                renderTimes(contest, output_times) + '<td>' +
                (!output_times ? '<a href="/arena/' + contest.alias + '/practice/">' + OmegaUp.T.wordsPractice + '</a>' : '') + '</td>' +
                '</tr>')
			);
		}
    }

    var omegaup = new OmegaUp();

    var contestLists = [
        // List Id, Active, Recommended.
        ['#current-contests', 'ACTIVE', 'NOT_RECOMMENDED'],
        ['#recommended-current-contests', 'ACTIVE', 'RECOMMENDED'],
        ['#past-contests', 'PAST', 'NOT_RECOMMENDED'],
        ['#recommended-past-contests', 'PAST', 'RECOMMENDED'],
    ];

    var requests = [];
    for (var i = 0, len = contestLists.length; i < len; i++) {
        requests.push(omegaup.getContests(
            (function(i) {
                return function (data) {
                    populateContestList($(contestLists[i][0]),
                                        data.results,
                                        contestLists[i][1]);
                };
            })(i),
            { 'active': contestLists[i][1],
              'recommended': contestLists[i][2],
              'page_size': 1000 }
        ));
    }

    // Wait until all of the calls above finish before showing the contents.
    $.when.apply($, requests).done(function() {
        $('#root').show();
        $('#loading').fadeOut('slow');
    });
});
