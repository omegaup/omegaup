$(function() {
    ko.bindingProvider.instance = new ko.secureBindingsProvider({attribute: 'data-bind'});
    var courseAlias = /\/course\/([^\/]+)/.exec(window.location.pathname)[1];
    omegaup.API.getCourseDetails(courseAlias, function(course) {
        var assignments = {};
        for (var i = 0; i < course.assignments.length; ++i) {
            // TODO(pablo): Agregar $progress$ al viewModel.
            var type = course.assignments[i].assignment_type;
            if (!assignments.hasOwnProperty(type)) {
                assignments[type] = [];
            }
            assignments[type].push(course.assignments[i]);
            course.assignments[i].startTime = omegaup.UI.formatDateTime(
                    new Date(1000*course.assignments[i].start_time));
            course.assignments[i].finishTime = omegaup.UI.formatDateTime(
                    new Date(1000*course.assignments[i].finish_time));
        }
        for (var type in assignments) {
            course[type] = assignments[type];
        }
        ko.applyBindings(course, $('#course-info')[0]);
    });
});
