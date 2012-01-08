$(document).ready(function() {
    var omegaup = new OmegaUp();

    omegaup.getContests(function(data) {
	var list = data.contests;

        for (var i = 0, len = list.length; i < len; i++) {
            $('#contest-list').append(
                $('<tr>' +
                    '<td><a href="/arena/' + list[i].alias + '">' + list[i].title + '</a></td>' +
                    '<td>' + list[i].description + '</td>' +
                    '<td>' + list[i].start_time + '</td>' +
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
