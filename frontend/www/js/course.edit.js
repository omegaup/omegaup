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

	omegaup.API.getCourseAdminDetails(courseAlias, function(course) {
		$('.page-header h1 span').html(omegaup.T.courseEdit + ' ' + course.name);
		$('.page-header h1 small').html('&ndash; <a href="/course/' + courseAlias + '/">' + omegaup.T.courseEditGoToCourse + '</a>');
		$(".new_course_form #title").val(course.name);
		$(".new_course_form #alias").val(course.alias);
		$(".new_course_form #description").val(course.description);
		$(".new_course_form #start_time").val(omegaup.UI.formatDateTime(course.start_time));
		$(".new_course_form #finish_time").val(omegaup.UI.formatDateTime(course.finish_time));

		if (course.window_length === null) {
			// Disable window length
			$('#window_length_enabled').removeAttr('checked');
			$('#window_length').val('');
		} else {
			$('#window_length_enabled').attr('checked', 'checked');
			$('#window_length').removeAttr('disabled');
			$('#window_length').val(course.window_length);
		}

		$(".new_course_form #show_scoreboard").val(course.show_scoreboard);

		$(".contest-publish-form #public").val(course.public);

		if (course.contestant_must_register == null ||
			course.contestant_must_register == "0"){
			$("#requests").hide();
		}
	});

	// Edit course
	$('.new_course_form').submit(updateCourse);

	// Update course
	function updateCourse() {
		omegaup.API.updateCourse(
			courseAlias,
			$(".new_course_form #title").val(),
			$(".new_course_form #description").val(),
			(new Date($(".new_course_form #start_time").val()).getTime()) / 1000,
			(new Date($(".new_course_form #finish_time").val()).getTime()) / 1000,
			$(".new_course_form #alias").val(),
			$(".new_course_form #show_scoreboard").val(),
			function(data) {
				if(data.status == "ok") {
					omegaup.UI.success('Tu curso ha sido editado! <a href="/course/' +
                            $('.new_course_form #alias').val() + '">' +
                            omegaup.T.courseEditGoToCourse + '</a>');
					$('div.post.footer').show();
					window.scrollTo(0, 0);
				} else {
					omegaup.UI.error(data.error || 'error');
				}
			}
		);
		return false;
	}
});
