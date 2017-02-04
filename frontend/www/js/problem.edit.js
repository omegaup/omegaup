omegaup.OmegaUp.on('ready', function() {
  var chosenLanguage = null;

  if (window.location.hash) {
    $('#sections').find('a[href="' + window.location.hash + '"]').tab('show');
  }

  $('#sections')
      .on('click', 'a', function(e) {
        e.preventDefault();
        // add this line
        window.location.hash = $(this).attr('href');
        $(this).tab('show');
      });

  var problemAlias = $('#problem-alias').val();
  refreshEditForm(problemAlias);

  // Add typeaheads
  refreshProblemAdmins();
  omegaup.UI.userTypeahead($('#username-admin'));
  omegaup.UI.typeahead($('#groupalias-admin'), omegaup.API.searchGroups,
                       function(event, val) {
                         $(event.target).attr('data-alias', val.value);
                       });

  refreshProblemTags();
  $('#tag-name')
      .typeahead(
          {
            minLength: 2,
            highlight: true,
          },
          {
            source: omegaup.UI.typeaheadWrapper(omegaup.API.searchTags),
            displayKey: 'name',
          })
      .on('typeahead:selected', function(event, val) {
        $(event.target).val(val.name);
      });

  $('#add-admin-form')
      .submit(function() {
        var username = $('#username-admin').val();

        omegaup.API.addAdminToProblem({
                     problem_alias: problemAlias,
                     usernameOrEmail: username,
                   })
            .then(function(response) {
              omegaup.UI.success(omegaup.T.adminAdded);
              $('div.post.footer').show();
              refreshProblemAdmins();
            });

        return false;  // Prevent refresh
      });

  $('#add-group-admin-form')
      .submit(function() {
        var groupalias = $('#groupalias-admin').attr('data-alias');

        omegaup.API.addGroupAdminToProblem(
            problemAlias, groupalias, function(response) {
              if (response.status === 'ok') {
                omegaup.UI.success(omegaup.T.adminAdded);
                $('div.post.footer').show();

                refreshProblemAdmins();
              } else {
                omegaup.UI.error(response.error || 'error');
              }
            });

        return false;  // Prevent refresh
      });

  $('#download form')
      .submit(function() {
        window.location = '/api/problem/download/problem_alias/' +
                          omegaup.UI.escape(problemAlias) + '/';
        return false;
      });

  $('#markdown form')
      .submit(function() {
        $('.has-error').removeClass('has-error');
        if ($('#markdown-message').val() == '') {
          omegaup.UI.error(omegaup.T['editFieldRequired']);
          $('#markdown-message-group').addClass('has-error');
          return false;
        }
      });

  function refreshProblemAdmins() {
    omegaup.API.getProblemAdmins(problemAlias, function(admins) {
      $('#problem-admins').empty();
      // Got the contests, lets populate the dropdown with them
      for (var i = 0; i < admins.admins.length; i++) {
        var admin = admins.admins[i];
        $('#problem-admins')
            .append(
                $('<tr></tr>')
                    .append(
                        $('<td></td>')
                            .append($('<a></a>')
                                        .attr('href', '/profile/' +
                                                          admin.username + '/')
                                        .text(admin.username)))
                    .append($('<td></td>').text(admin.role))
                    .append((admin.role != 'admin') ?
                                $('<td></td>') :
                                $('<td><button type="button" class="close">' +
                                  '&times;</button></td>')
                                    .click((function(username) {
                                      return function(e) {
                                        omegaup.API.removeAdminFromProblem({
                                                     problem_alias:
                                                         problemAlias,
                                                     usernameOrEmail: username,
                                                   })
                                            .then(function(response) {
                                              omegaup.UI.success(
                                                  omegaup.T.adminAdded);
                                              $('div.post.footer').show();
                                              var tr = e.target.parentElement
                                                           .parentElement;
                                              $(tr).remove();
                                            });
                                      };
                                    })(admin.username))));
      }
      $('#problem-group-admins').empty();
      // Got the contests, lets populate the dropdown with them
      for (var i = 0; i < admins.group_admins.length; i++) {
        var group_admin = admins.group_admins[i];
        $('#problem-group-admins')
            .append(
                $('<tr></tr>')
                    .append(
                        $('<td></td>')
                            .append($('<a></a>')
                                        .attr('href', '/group/' +
                                                          group_admin.alias +
                                                          '/edit/')
                                        .text(group_admin.name)))
                    .append($('<td></td>').text(group_admin.role))
                    .append((group_admin.role != 'admin') ?
                                $('<td></td>') :
                                $('<td><button type="button" class="close">' +
                                  '&times;</button></td>')
                                    .click((function(alias) {
                                      return function(e) {
                                        omegaup.API.removeGroupAdminFromProblem(
                                            problemAlias, alias,
                                            function(response) {
                                              if (response.status == 'ok') {
                                                omegaup.UI.success(
                                                    omegaup.T.adminAdded);
                                                $('div.post.footer').show();
                                                var tr = e.target.parentElement
                                                             .parentElement;
                                                $(tr).remove();
                                              } else {
                                                omegaup.UI.error(
                                                    response.error || 'error');
                                              }
                                            });
                                      };
                                    })(group_admin.alias))));
      }
    });
  }

  $('#add-tag-form')
      .submit(function() {
        var tagname = $('#tag-name').val();
        var public = $('#tag-public').val();

        omegaup.API.addTagToProblem(
            problemAlias, tagname, public, function(response) {
              if (response.status === 'ok') {
                omegaup.UI.success('Tag successfully added!');
                $('div.post.footer').show();

                refreshProblemTags();
              } else {
                omegaup.UI.error(response.error || 'error');
              }
            });

        return false;  // Prevent refresh
      });

  function refreshProblemTags() {
    omegaup.API.getProblemTags(problemAlias, function(result) {
      $('#problem-tags').empty();
      // Got the contests, lets populate the dropdown with them
      for (var i = 0; i < result.tags.length; i++) {
        var tag = result.tags[i];
        $('#problem-tags')
            .append(
                $('<tr></tr>')
                    .append($('<td></td>')
                                .append($('<a></a>')
                                            .attr('href',
                                                  '/problem/?tag=' + tag.name)
                                            .text(tag.name)))
                    .append($('<td></td>').text(tag.public))
                    .append($('<td><button type="button" class="close">' +
                              '&times;</button></td>')
                                .click((function(tagname) {
                                  return function(e) {
                                    omegaup.API.removeTagFromProblem(
                                        problemAlias, tagname,
                                        function(response) {
                                          if (response.status == 'ok') {
                                            omegaup.UI.success(
                                                'Tag successfully removed!');
                                            $('div.post.footer').show();
                                            var tr = e.target.parentElement
                                                         .parentElement;
                                            $(tr).remove();
                                          } else {
                                            omegaup.UI.error(response.error ||
                                                             'error');
                                          }
                                        });
                                  };
                                })(tag.name))));
      }
    });
  }

  var md_converter = Markdown.getSanitizingConverter();
  md_editor = new Markdown.Editor(md_converter, '-statement');  // Global.
  md_editor.run();

  function refreshEditForm(problemAlias) {
    if (problemAlias === '') {
      $('input[name=title]').val('');
      $('input[name=time_limit]').val('');
      $('input[name=validator_time_limit]').val('');
      $('input[name=overall_wall_time_limit]').val('');
      $('input[name=extra_wall_time]').val('');
      $('input[name=memory_limit]').val('');
      $('input[name=output_limit]').val('');
      $('input[name=source]').val('');
      $('input[name=stack_limit]').val('');
      return;
    }

    omegaup.API.getProblem(null, problemAlias, problemCallback, 'markdown');
  }

  function problemCallback(problem) {
    $('.page-header h1 span')
        .html(omegaup.T.problemEditEditProblem + ' ' + problem.title);
    $('.page-header h1 small')
        .html('&ndash; <a href="/arena/problem/' + problemAlias + '/">' +
              omegaup.T.problemEditGoToProblem + '</a>');
    $('input[name=title]').val(problem.title);
    $('#statement-preview .title').html(omegaup.UI.escape(problem.title));
    $('input[name=time_limit]').val(problem.time_limit);
    $('input[name=validator_time_limit]').val(problem.validator_time_limit);
    $('input[name=overall_wall_time_limit]')
        .val(problem.overall_wall_time_limit);
    $('input[name=extra_wall_time]').val(problem.extra_wall_time);
    $('input[name=memory_limit]').val(problem.memory_limit);
    $('input[name=output_limit]').val(problem.output_limit);
    $('input[name=stack_limit]').val(problem.stack_limit);
    $('input[name=source]').val(problem.source);
    $('#statement-preview .source').html(omegaup.UI.escape(problem.source));
    $('#statement-preview .problemsetter')
        .attr('href', '/profile/' + problem.problemsetter.username + '/')
        .html(omegaup.UI.escape(problem.problemsetter.name));
    $('input[name=email_clarifications][value=' + problem.email_clarifications +
      ']')
        .attr('checked', 1);
    $('select[name=validator]').val(problem.validator);
    $('input[name=public][value=' + problem.public + ']').attr('checked', 1);
    $('#languages').val(problem.languages);
    $('input[name=alias]').val(problemAlias);
    if (chosenLanguage == null ||
        chosenLanguage == problem.problem_statement_language) {
      chosenLanguage = problem.problem_statement_language;
      $('#wmd-input-statement').val(problem.problem_statement);
      $('#statement-language').val(problem.problem_statement_language);
    } else {
      $('#wmd-input-statement').val('');
    }
    md_editor.refreshPreview();
    if (problem.slow == 1) {
      $('.slow-warning').show();
    }
  }

  $('#statement-preview-link')
      .on('show.bs.tab', function(e) {
        MathJax.Hub.Queue(['Typeset', MathJax.Hub, $('#wmd-preview').get(0)]);
      });

  $('#statement-language')
      .on('change', function(e) {
        chosenLanguage = $('#statement-language').val();
        omegaup.API.getProblem(null, problemAlias, problemCallback, 'markdown',
                               false, chosenLanguage);
      });
});
