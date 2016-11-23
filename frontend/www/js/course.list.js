$('document').ready(function() {
    function updateViewModel(model, data, section) {
        var current = model[section + 'CoursesCurrent'];
        var past = model[section + 'CoursesPast'];
        for (var i = 0; i < data[section].length; ++i) {
            var course = data[section][i];
            course.courseURL = '/course/' + course.alias;
            course.endDate = omegaup.UI.formatDate(course.finish_time);
            course.numHomeworks = course.counts.homework;
            course.numTests = course.counts.test;
            if (course.finish_time > Date.now()) {
                current.push(course);
            } else {
                past.push(course);
            }
        }
    }
    omegaup.API.getCourseList().then(function(data) {
        if (data.status != 'ok') {
            omegaup.UI.error(data.error);
            return;
        }
        var viewModel = {
            adminCoursesCurrent: ko.observableArray(),
            adminCoursesPast: ko.observableArray(),
            studentCoursesCurrent: ko.observableArray(),
            studentCoursesPast: ko.observableArray(),
        };

        updateViewModel(viewModel, data, 'admin');
        updateViewModel(viewModel, data, 'student');
        ko.applyBindings(viewModel);
    });
});
