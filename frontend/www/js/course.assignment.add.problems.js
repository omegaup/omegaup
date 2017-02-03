omegaup.OmegaUp.on('ready', function() {
  $('.assignment-add-problem')
      .submit(function() {
        var courseAlias =
            /\/course\/([^\/]+)\/edit\/?.*/.exec(window.location.pathname)[1];

        omegaup.API.addCourseAssignmentProblem({
                     course_alias: courseAlias,
                     assignment_alias:
                         $('.assignment-add-problem #assignments-list').val(),
                     problem_alias:
                         $('.assignment-add-problem #problems-dropdown').val()
                   })
            .then(function(data) {
              omegaup.UI.success(omegaup.T.courseAssignmentProblemAdded);
            });
        return false;
      });

  var list = $('#list-problems');
  function updateProblemList() {
    var topic = $('#topic-list').val();
    var level = $('#level-list').val();
    var tags = [topic, level];
    omegaup.API.getProblemsWithTags(tags).then(function(data) {
      var problems = data.results;
      var n = problems.length;
      list.empty();
      for (var i = 0; i < n; ++i) {
        list.append(
            $('<option>').text(problems[i].title).val(problems[i].alias));
      }
    });
  }

  $('#topic-list, #level-list').change(updateProblemList);
  $('#list-problems')
      .change(function() { $('#problems-dropdown')
                               .val(list.val()); });
  updateProblemList();
});
