$('document').ready(function() {
	if(window.location.hash){
		$('#sections').find('a[href="'+window.location.hash+'"]').tab('show');
	}

	$('#sections').on('click', 'a', function (e) {
		e.preventDefault();
		// add this line
		window.location.hash = $(this).attr('href');
		$(this).tab('show');
	});

	var courseAlias = /\/course\/([^\/]+)\/edit\/?.*/.exec(window.location.pathname)[1];

	/* @TODO
	omegaup.API.getCourseAdminDetails(courseAlias, function(contest) {
		$('.page-header h1 span').html(omegaup.T['contestEdit'] + ' ' + contest.title);
		$('.page-header h1 small').html('&ndash; <a href="/arena/' + courseAlias + '/">' + omegaup.T['contestDetailsGoToContest'] + '</a>');
		$(".new_course_form #title").val(contest.title);
		$(".new_course_form #alias").val(contest.alias);
		$(".new_course_form #description").val(contest.description);
		$(".new_course_form #start_time").val(omegaup.UI.formatDateTime(contest.start_time));
		$(".new_course_form #finish_time").val(omegaup.UI.formatDateTime(contest.finish_time));

		if (contest.window_length === null) {
			// Disable window length
			$('#window_length_enabled').removeAttr('checked');
			$('#window_length').val('');
		} else {
			$('#window_length_enabled').attr('checked', 'checked');
			$('#window_length').removeAttr('disabled');
			$('#window_length').val(contest.window_length);
		}

		$(".new_course_form #points_decay_factor").val(contest.points_decay_factor);
		$(".new_course_form #submissions_gap").val(contest.submissions_gap / 60);
		$(".new_course_form #feedback").val(contest.feedback);
		$(".new_course_form #penalty").val(contest.penalty);
		$(".new_course_form #public").val(contest.public);
		$(".new_course_form #register").val(contest.contestant_must_register);
		$(".new_course_form #scoreboard").val(contest.scoreboard);
		$(".new_course_form #penalty_type").val(contest.penalty_type);
		$(".new_course_form #show_scoreboard_after").val(contest.show_scoreboard_after);

		$(".contest-publish-form #public").val(contest.public);

		if (contest.contestant_must_register == null ||
				contest.contestant_must_register == "0"){
			$("#requests").hide();
		}
	});
	" */

	// Edit contest
	$('.new_course_form').submit(function() {
		return updateCourse($(".new_course_form #public").val());
	});

	// Update contest
	function updateContest(public) {
		omegaup.API.updateCourse(
			courseAlias,
			$(".new_course_form #title").val(),
			$(".new_course_form #description").val(),
			(new Date($(".new_course_form #start_time").val()).getTime()) / 1000,
			(new Date($(".new_course_form #finish_time").val()).getTime()) / 1000,
			$(".new_course_form #alias").val(),
			public,
			$(".new_course_form #show_scoreboard").val(),
			function(data) {
				if(data.status == "ok") {
					omegaup.UI.success('Tu curso ha sido editado! <a href="/arena/'+ $('.new_course_form #alias').val() + '">' + omegaup.T['courseEditGoToCourse'] + '</a>');
					$('div.post.footer').show();
					window.scrollTo(0,0);
				} else {
					omegaup.UI.error(data.error || 'error');
				}
			}
		);
		return false;
	}
});
