
$(document)
    .ready(function() {
      var interviewAlias =
          /\/interview\/([^\/]+)\/?.*/.exec(window.location.pathname)[1];

      if (window.location.hash) {
        $('#sections')
            .find('a[href="' + window.location.hash + '"]')
            .tab('show');
      }

      $('#sections')
          .on('click', 'a', function(e) {
            e.preventDefault();
            // add this line
            window.location.hash = $(this).attr('href');
            $(this).tab('show');
          });

      $('form#add_user_to_interview')
          .submit(function() {
            var userOrEmail = $('#usernameOrEmail').val();
            var html = '<tr>' +
                       '<td>' + omegaup.UI.escape(userOrEmail) + '</td>' +
                       '</tr>';

            InvitedUsers.push(userOrEmail);
            $('#invitepeople > table > tbody').append(html);
            $('#send_invites').show();
            $('#usernameOrEmail').val('');

            return false;  // Prevent page refresh on submit
          });

      var InvitedUsers = Array();

      $('form#send_invites')
          .submit(function() {
            omegaup.API.addUsersToInterview(
                interviewAlias, InvitedUsers.join(), function(response) {
                  if (response.status == 'ok') {
                    omegaup.UI.success(omegaup.T['userEditSuccess']);
                    InvitedUsers = Array();
                    fillCandidatesTable();
                    $('#invitepeople > table > tbody').html('');
                  } else {
                    omegaup.UI.error(response.error);
                    fillCandidatesTable();
                  }
                });
            return false;  // Prevent page refresh on submit
          });

      omegaup.API.getContestAdminDetails(interviewAlias, function(contest) {
        $('.page-header h1 span')
            .html(omegaup.T['interviewEdit'] + ' ' + contest.title);
        $('.page-header h1 small')
            .html('&ndash; <a href="/interview/' + interviewAlias + '/arena">' +
                  omegaup.T['interviewGoToInterview'] + '</a>');
        $('.new_interview_form #title').val(contest.title);
        $('.new_interview_form #description').val(contest.description);
        $('#window_length').val(contest.window_length);
      });

      function fillCandidatesTable() {
        omegaup.API.getInterview(interviewAlias, function(interview) {
          var html = '';
          for (var i = 0; i < interview.users.length; i++) {
            html += '<tr>' +
                    '<td>' + omegaup.UI.escape(interview.users[i].username) +
                    '</td>' +
                    '<td>' + interview.users[i].email + '</td>' +
                    '<td>' + (interview.users[i].opened_interview ?
                                  interview.users[i].access_time :
                                  omegaup.T['interviewNotStarted']) +
                    '</td>' +
                    '<td>' + "<a href='result/" +
                    omegaup.UI.escape(interview.users[i].username) + "' >" +
                    "<button  class='btn btn-xs'>" + omegaup.T['wordsDetails'] +
                    '</button>' +
                    '</a>' +
                    '&nbsp;' + "<button  class='btn btn-xs'>" +
                    omegaup.T['resendInterviewEmail'] + '</button>' +
                    '</td>' +
                    '</tr>';
          }

          $('#candidate_list > table > tbody').empty().html(html);
        });
      }

      $('#add-problem-form')
          .submit(function() {
            problemAlias = $('input#problems-dropdown').val();
            points = $('input#points').val();
            order = $('input#order').val();

            omegaup.API.addProblemToContest(
                interviewAlias, order, problemAlias, points,
                function(response) {
                  if (response.status == 'ok') {
                    omegaup.UI.success('Problem successfully added!');
                    $('div.post.footer').show();
                    refreshContestProblems();
                  } else {
                    omegaup.UI.error(response.error || 'Error');
                  }
                });

            return false;  // Prevent page refresh
          });

      function refreshContestProblems() {
        omegaup.API.contestProblems(interviewAlias, function(response) {
          var problems = $('#contest-problems-table');
          problems.empty();

          for (var i = 0; i < response.problems.length; i++) {
            problems.append(
                $('<tr></tr>')
                    .append($('<td></td>').text(response.problems[i].order))
                    .append(
                        $('<td></td>')
                            .append($('<a></a>')
                                        .attr('href',
                                              '/arena/problem/' +
                                                  response.problems[i].alias +
                                                  '/')
                                        .text(response.problems[i].alias)))
                    .append($('<td></td>').text(response.problems[i].points))
                    .append(
                        $('<td><button type="button" class="close">' +
                          '&times;</button></td>')
                            .click((function(problem) {
                              return function(e) {
                                omegaup.API.removeProblemFromContest(
                                    interviewAlias, problem,
                                    function(response) {
                                      if (response.status == 'ok') {
                                        omegaup.UI.success(
                                            'Problem successfully removed!');
                                        $('div.post.footer').show();
                                        $(e.target.parentElement.parentElement)
                                            .remove();
                                      } else {
                                        omegaup.UI.error(response.error ||
                                                         'error');
                                      }
                                    });
                              };
                            })(response.problems[i].alias))));
          }
        });
      }

      omegaup.API.getProblems(function(problems) {
        // Got the problems, lets populate the dropdown with them
        for (var i = 0; i < problems.results.length; i++) {
          problem = problems.results[i];
          $('select#problems')
              .append($('<option></option>')
                          .attr('value', problem.alias)
                          .text(problem.title));
        }
      });

      omegaup.UI.problemTypeahead($('#problems-dropdown'));

      // Edit users
      omegaup.UI.userTypeahead($('#username-admin'));
      omegaup.UI.userTypeahead($('#usernameOrEmail'));
      omegaup.UI.typeahead($('#groupalias-admin'), omegaup.API.searchGroups);

      $('#add-admin-form')
          .submit(function() {
            var username = $('#username-admin').val();

            omegaup.API.addAdminToContest(
                interviewAlias, username, function(response) {
                  if (response.status == 'ok') {
                    omegaup.UI.success(omegaup.T['adminAdded']);
                    $('div.post.footer').show();

                    refreshContestAdmins();
                  } else {
                    omegaup.UI.error(response.error || 'error');
                  }
                });

            return false;  // Prevent refresh
          });

      // Add admin
      function refreshContestAdmins() {
        omegaup.API.getContestAdmins(interviewAlias, function(admins) {
          $('#contest-admins').empty();
          // Got the contests, lets populate the dropdown with them
          for (var i = 0; i < admins.admins.length; i++) {
            var admin = admins.admins[i];
            $('#contest-admins')
                .append(
                    $('<tr></tr>')
                        .append($('<td></td>')
                                    .append($('<a></a>')
                                                .attr('href',
                                                      '/profile/' +
                                                          admin.username + '/')
                                                .text(admin.username)))
                        .append($('<td></td>').text(admin.role))
                        .append(
                            (admin.role != 'admin') ?
                                $('<td></td>') :
                                $('<td><button type="button" class="close">' +
                                  '&times;</button></td>')
                                    .click((function(username) {
                                      return function(e) {
                                        omegaup.API.removeAdminFromContest(
                                            interviewAlias, username,
                                            function(response) {
                                              if (response.status == 'ok') {
                                                omegaup.UI.success(
                                                    omegaup.T['adminAdded']);
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
                                    })(admin.username))));
          }
          $('#contest-group-admins').empty();
          for (var i = 0; i < admins.group_admins.length; i++) {
            var group_admin = admins.group_admins[i];
            $('#contest-group-admins')
                .append(
                    $('<tr></tr>')
                        .append($('<td></td>')
                                    .append($('<a></a>')
                                                .attr('href',
                                                      '/group/' +
                                                          group_admin.alias +
                                                          '/edit/')
                                                .text(group_admin.name)))
                        .append($('<td></td>').text(group_admin.role))
                        .append(
                            (group_admin.role != 'admin') ?
                                $('<td></td>') :
                                $('<td><button type="button" class="close">' +
                                  '&times;</button></td>')
                                    .click((function(alias) {
                                      return function(e) {
                                        omegaup.API.removeGroupAdminFromContest(
                                            interviewAlias, alias,
                                            function(response) {
                                              if (response.status == 'ok') {
                                                omegaup.UI.success(
                                                    omegaup.T['adminAdded']);
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
      $('#add-group-admin-form')
          .submit(function() {
            var groupalias = $('#groupalias-admin').val();

            omegaup.API.addGroupAdminToContest(
                interviewAlias, groupalias, function(response) {
                  if (response.status == 'ok') {
                    omegaup.UI.success(omegaup.T['adminAdded']);
                    $('div.post.footer').show();

                    refreshContestAdmins();
                  } else {
                    omegaup.UI.error(response.error || 'error');
                  }
                });

            return false;  // Prevent refresh
          });

      refreshContestProblems();
      refreshContestAdmins();
      fillCandidatesTable();
    });
