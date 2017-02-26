(function() {
  $('.navbar #nav-problems').addClass('active');

  function fillProblemsTable() {
    var deferred = $('#show-admin-problems').prop('checked') ?
                       omegaup.API.Problem.adminList() :
                       omegaup.API.Problem.myList();
    deferred.then(function(result) {
              $('#problem-list .added').remove();
              for (var i = 0; i < result.problems.length; i++) {
                var row = $('#problem-list .problem-list-template')
                              .clone()
                              .removeClass('problem-list-template')
                              .addClass('added');
                var problem = result.problems[i];
                $('input[type="checkbox"]', row).attr('id', problem.alias);
                $('.title', row)
                    .attr('href', '/arena/problem/' + problem.alias + '/')
                    .html(omegaup.UI.escape(problem.title));
                if (problem.tags && problem.tags.length > 0) {
                  var tags = $('.tag-list', row).removeClass('hidden');
                  for (var j = 0; j < problem.tags.length; j++) {
                    tags.append($('<a class="tag"></a>')
                                    .attr('href', '/problem/?tag=' +
                                                      omegaup.UI.escape(
                                                          problem.tags[j]))
                                    .html(omegaup.UI.escape(problem.tags[j])));
                  }
                }
                if (problem.public != '1')
                  $('.private', row).removeClass('hidden');
                $('.edit', row)
                    .attr('href', '/problem/' + problem.alias + '/edit/');
                $('.stats', row)
                    .attr('href', '/problem/' + problem.alias + '/stats/');
                $('#problem-list').append(row);
              }
              $('#problem-list').removeClass('wait_for_ajax');
            })
        .fail(omegaup.UI.apiError);
  }
  fillProblemsTable();

  $('#show-admin-problems').click(fillProblemsTable);

  function makePublic(isPublic) {
    return function() {
      omegaup.UI.bulkOperation(
          function(alias, resolve, reject) {
            omegaup.API.Problem.update({
                                 problem_alias: alias,
                                 'public': isPublic ? 1 : 0,
                                 message: isPublic ? 'private -> public' :
                                                     'public -> private',
                               })
                .then(resolve)
                .fail(reject);
          },
          function() { fillProblemsTable(); });
    };
  }

  $('#bulk-make-public').click(makePublic(true));
  $('#bulk-make-private').click(makePublic(false));
})();
