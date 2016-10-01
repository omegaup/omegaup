$(document).ready(function() {
	var courseAlias = /\/course\/([^\/]+)\/?/.exec(window.location.pathname)[1];

	function onCourseLoaded(course) {
		if (course.status == 'ok') {
			$('.course #title').html(omegaup.UI.escape(course.title));
			$('.course #description').html(omegaup.UI.escape(course.description));
			$('.course #start_time').html(omegaup.UI.formatDateTime(course.start_time));
			$('.course #finish_time').html(omegaup.UI.formatDateTime(course.finish_time));
			$('.course #title').html(omegaup.UI.escape(course.title));
		}
	}

	omegaup.API.getCourseDetails(courseAlias, onCourseLoaded);
});

