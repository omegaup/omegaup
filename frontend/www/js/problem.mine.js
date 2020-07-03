(function () {
  function fillProblemsTable() {
    ($('#show-admin-problems').prop('checked')
      ? omegaup.API.Problem.adminList()
      : omegaup.API.Problem.myList()
    )
      .then(function (result) {
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
            .text(problem.title);
          if (problem.tags && problem.tags.length > 0) {
            var tags = $('.tag-list', row).removeClass('hidden');
            for (var j = 0; j < problem.tags.length; j++) {
              var tagClass =
                problem.tags[j].source === 'voted'
                  ? 'tag tag-voted pull-left'
                  : 'tag pull-left';
              tags.append(
                $('<a></a>')
                  .addClass(tagClass)
                  .attr(
                    'href',
                    '/problem/?tag[]=' +
                      omegaup.UI.escape(problem.tags[j].name),
                  )
                  .text(problem.tags[j].name),
              );
            }
          }
          if (problem.visibility <= 0) {
            $('.private', row).removeClass('hidden');
            if (problem.visibility == -10) {
              $('.private', row).removeClass('glyphicon-eye-close');
              $('.private', row).addClass('glyphicon-trash');
              $('.private', row).prop('title', omegaup.T.wordsDeleted);
            }
          }
          $('.edit', row).attr('href', '/problem/' + problem.alias + '/edit/');
          $('.stats', row).attr(
            'href',
            '/problem/' + problem.alias + '/stats/',
          );
          if (problem.visibility == -10) {
            $('input[type=checkbox]', row).prop('disabled', 'disabled');
          }
          $('#problem-list').append(row);
        }
        $('#problem-list').removeClass('wait_for_ajax');
      })
      .catch(omegaup.UI.apiError);
  }
  fillProblemsTable();

  $('#show-admin-problems').on('click', fillProblemsTable);

  function makePublic(isPublic) {
    return function () {
      var promises = [];
      $('input[type=checkbox]').each(function () {
        if (this.checked) {
          promises.push(
            omegaup.API.Problem.update({
              problem_alias: this.id,
              visibility: isPublic ? 1 : 0,
              message: isPublic ? 'private -> public' : 'public -> private',
            }),
          );
        }
      });

      Promise.all(promises)
        .then(() => {
          omegaup.UI.success(omegaup.T.updateItemsSuccess);
        })
        .catch((error) => {
          omegaup.UI.error(
            omegaup.UI.formatString(omegaup.T.bulkOperationError, error),
          );
        })
        .finally(() => {
          fillProblemsTable();
        });
    };
  }

  $('#bulk-make-public').on('click', makePublic(true));
  $('#bulk-make-private').on('click', makePublic(false));
})();
