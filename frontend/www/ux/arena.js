$(document).ready(function() {
  
    function makeWorldClockLink(date) {
        return "http://timeanddate.com/worldclock/fixedtime.html?iso=" + date.toISOString();
    }
    function timeLeft(start) {
    	var now = new Date();
    	var left = new Date(then - now);
    	return left.toISOString().substr(11, 8);
    }
    var omegaup = new OmegaUp();

    omegaup.getContests(function(data) {
        var list = data.contests;
        var current = $('#contest-list');
        var past = $('#past-contests');
        var now = new Date();
        
        for (var i = 0, len = list.length; i < len; i++) {
            var start = new Date(list[i].start_time);
            var end = new Date(list[i].finish_time);
            ((end > now) ? current : past).append(
                $('<tr>' +
                    '<td><a href="/arena/' + list[i].alias + '">' + list[i].title + '</a></td>' +
                    '<td>' + list[i].description + '</td>' +
                    '<td><a href="' + makeWorldClockLink(start) + '">' + list[i].start_time + '</a></td>' +
                    '<td>' + list[i].finish_time + '</td>' +
                '</tr>')
            );
        }

        $('#loading').fadeOut('slow');
        $('#root').fadeIn('slow');
    });

    $('#contest-list tr').live('click', function() {

    });
});
