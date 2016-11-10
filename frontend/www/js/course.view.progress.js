$('document')
    .ready(function() {
      var courseAlias =
          /\/course\/([^\/]+)\/edit\/?.*/.exec(window.location.pathname)[1];

      ko.bindingProvider.instance =
          new ko.secureBindingsProvider({attribute: 'data-bind'});

      omegaup.API.getCourseStudentList(courseAlias, function(data) {
        if (data.status != 'ok') {
          omegaup.UI.error(data.error);
          return;
        }

        var koStudentsList = {getStudentsList: ko.observableArray()};
        for (var i = 0; i < data['students'].length; ++i) {
          var student = data['students'][i];
          student.profileURL = '/profile/' + student.username,

          totalHomeworks = (data['counts']['homework'] != null) ?
                               data['counts']['homework'] :
                               0;
          totalTests =
              (data['counts']['test'] != null) ? data['counts']['test'] : 0;
          student.totalHomeworks =
              student.count_homeworks_done + '/' + totalHomeworks;
          student.totalTests = student.count_tests_done + '/' + totalTests;

          koStudentsList['getStudentsList'].push(student);
        }

        ko.applyBindings(koStudentsList);
      });
    });
