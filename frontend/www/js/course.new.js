$('document').ready(function() {
	$('.new_course_form').submit(function() {
		omegaup.API.createCourse(
				$(".new_course_form #title").val(),
				$(".new_course_form #description").val(),
				(new Date($(".new_course_form #start_time").val()).getTime()) / 1000,
				(new Date($(".new_course_form #finish_time").val()).getTime()) / 1000,
				$(".new_course_form #alias").val(),
				$(".new_course_form #public").val(),
				$(".new_course_form #show_scoreboard").val(),
				function(data) {
					if(data.status == "ok") {
						window.location.replace('/course/'+ $('.new_course_form #alias').val() + '/edit/#problems');
					} else {
						omegaup.UI.error(data.error || 'error');
					}
				}
		);

		return false;
	});
});
