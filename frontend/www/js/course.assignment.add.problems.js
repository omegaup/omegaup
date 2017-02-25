omegaup.OmegaUp.on('ready', function() {
  var courseAlias =
      /\/course\/([^\/]+)\/edit\/?.*/.exec(window.location.pathname)[1];

  function refreshProblems() {
    var assignmentAlias = $('.assignment-add-problem #assignments-list').val();
    if (!assignmentAlias || 0 === assignmentAlias.length) {
      return;
    }

    omegaup.API.Course.getAssignment(
                          {assignment: assignmentAlias, course: courseAlias})
        .then(function(response) {
          var problems = $('#assignment-problems');
          problems.empty();

          $.each(response.problems, function(index, item) {
            problems.append(
                $('<tr></tr>')
                    .append($('<td></td>').text(item.alias))
                    .append(
                        $('<td><button type="button" class="close">' +
                          '&times;</button></td>')
                            .click((function(problemAlias) {
                              return function(e) {
                                omegaup.API.Course.removeProblem({
                                                    course_alias: courseAlias,
                                                    problem_alias: problemAlias,
                                                    assignment_alias:
                                                        assignmentAlias,
                                                  })
                                    .then(function(response) {
                                      omegaup.UI.success('successfulOperation');
                                      refreshProblems();
                                    })
                                    .fail(omegaup.UI.apiError);
                              };
                            })(item.alias))));
          });
        })
        .fail(omegaup.UI.apiError);
  }

  $('.assignment-add-problem')
      .submit(function() {
        omegaup.API.Course
            .addProblem({
              course_alias: courseAlias,
              assignment_alias:
                  $('.assignment-add-problem #assignments-list').val(),
              problem_alias:
                  $('.assignment-add-problem #problems-dropdown').val()
            })
            .then(function(data) {
              refreshProblems();
              omegaup.UI.success(omegaup.T.courseAssignmentProblemAdded);
            });
        return false;
      });

  var list = $('#list-problems');
  function updateProblemList() {
    var topic = $('#topic-list').val();
    var level = $('#level-list').val();
    var tags = [topic, level];
    omegaup.API.Problem.list({tag: tags})
        .then(function(data) {
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

  $('.assignment-add-problem #assignments-list').change(refreshProblems);
  refreshProblems();
});

$(function() {
  $('a[data-toggle="tab"]')
      .on('shown.bs.tab', function(e) {
        var target = $(e.target).attr('href');

        // If add-problems tab is on focus
        if (target === '#add-problems') {
          var courseAlias =
              /\/course\/([^\/]+)\/edit\/?.*/.exec(window.location.pathname)[1];

          // Fill assignments
          omegaup.API.Course.listAssignments({course_alias: courseAlias})
              .then(function(data) {
                $('.assignment-add-problem #assignments-list').empty();

                $.each(data.assignments, function(index, item) {
                  $('.assignment-add-problem #assignments-list')
                      .append(
                          $('<option/>', {value: item.alias, text: item.name}));
                });

                $('.assignment-add-problem #assignments-list').change();
              });

          // Plug problems type-ahead
          omegaup.UI.problemTypeahead(
              $('.assignment-add-problem #problems-dropdown'));
        }
      });
});
