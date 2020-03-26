omegaup.OmegaUp.on('ready', function() {
  var interviewAlias = /\/interview\/([^\/]+)\/?.*/.exec(
    window.location.pathname,
  )[1];

  if (window.location.hash) {
    $('#sections')
      .find('a[href="' + window.location.hash + '"]')
      .tab('show');
  }

  $('#sections').on('click', 'a', function(e) {
    e.preventDefault();
    // add this line
    window.location.hash = $(this).attr('href');
    $(this).tab('show');
  });

  $('form#add_user_to_interview').on('submit', function(ev) {
    ev.preventDefault();
    var userOrEmail = $('#usernameOrEmail').val();
    var html =
      '<tr>' + '<td>' + omegaup.UI.escape(userOrEmail) + '</td>' + '</tr>';

    InvitedUsers.push(userOrEmail);
    $('#invitepeople > table > tbody').append(html);
    $('#send_invites').show();
    $('#usernameOrEmail').val('');
  });

  var InvitedUsers = Array();

  $('form#send_invites').on('submit', function(ev) {
    ev.preventDefault();
    omegaup.API.Interview.addUsers({
      interview_alias: interviewAlias,
      usernameOrEmailsCSV: InvitedUsers.join(),
    })
      .then(function(response) {
        omegaup.UI.success(omegaup.T.userEditSuccess);
        InvitedUsers = Array();
        fillCandidatesTable();
        $('#invitepeople > table > tbody').html('');
      })
      .catch(function(response) {
        omegaup.UI.error(response.error);
        fillCandidatesTable();
      });
  });

  omegaup.API.Contest.adminDetails({ contest_alias: interviewAlias })
    .then(function(contest) {
      $('.page-header h1 span').html(
        omegaup.T.interviewEdit +
          ' ' +
          omegaup.UI.escape(omegaup.UI.contestTitle(contest)),
      );
      $('.page-header h1 small').html(
        '&ndash; <a href="/interview/' +
          interviewAlias +
          '/arena">' +
          omegaup.T.interviewGoToInterview +
          '</a>',
      );
      $('.new_interview_form #title').val(omegaup.UI.contestTitle(contest));
      $('.new_interview_form #description').val(contest.description);
      $('#window_length').val(contest.window_length);
    })
    .catch(omegaup.UI.apiError);

  function fillCandidatesTable() {
    omegaup.API.Interview.details({ interview_alias: interviewAlias })
      .then(function(interview) {
        var html = '';
        for (var i = 0; i < interview.users.length; i++) {
          html +=
            '<tr>' +
            '<td>' +
            omegaup.UI.escape(interview.users[i].username) +
            '</td>' +
            '<td>' +
            interview.users[i].email +
            '</td>' +
            '<td>' +
            (interview.users[i].opened_interview
              ? interview.users[i].access_time
              : omegaup.T.interviewNotStarted) +
            '</td>' +
            '<td>' +
            "<a href='result/" +
            omegaup.UI.escape(interview.users[i].username) +
            "' >" +
            "<button  class='btn btn-xs'>" +
            omegaup.T.wordsDetails +
            '</button>' +
            '</a>' +
            '&nbsp;' +
            "<button  class='btn btn-xs'>" +
            omegaup.T.resendInterviewEmail +
            '</button>' +
            '</td>' +
            '</tr>';
        }

        $('#candidate_list > table > tbody')
          .empty()
          .html(html);
      })
      .catch(omegaup.UI.apiError);
  }

  $('#add-problem-form').on('submit', function() {
    omegaup.API.Contest.addProblem({
      contest_alias: interviewAlias,
      order_in_contest: $('input#order').val(),
      problem_alias: $('input#problems-dropdown').val(),
      points: $('input#points').val(),
    })
      .then(function(response) {
        omegaup.UI.success('Problem successfully added!');
        $('div.post.footer').show();
        refreshContestProblems();
      })
      .catch(omegaup.UI.apiError);

    return false; // Prevent page refresh
  });

  function refreshContestProblems() {
    omegaup.API.Contest.problems({ contest_alias: interviewAlias })
      .then(function(response) {
        var problems = $('#contest-problems-table');
        problems.empty();

        for (var i = 0; i < response.problems.length; i++) {
          problems.append(
            $('<tr></tr>')
              .append($('<td></td>').text(response.problems[i].order))
              .append(
                $('<td></td>').append(
                  $('<a></a>')
                    .attr(
                      'href',
                      '/arena/problem/' + response.problems[i].alias + '/',
                    )
                    .text(response.problems[i].alias),
                ),
              )
              .append($('<td></td>').text(response.problems[i].points))
              .append(
                $(
                  '<td><button type="button" class="close">' +
                    '&times;</button></td>',
                ).on(
                  'click',
                  (function(problem) {
                    return function(e) {
                      omegaup.API.Contest.removeProblem({
                        contest_alias: interviewAlias,
                        problem_alias: problem,
                      })
                        .then(function(response) {
                          omegaup.UI.success('Problem successfully removed!');
                          $('div.post.footer').show();
                          $(e.target.parentElement.parentElement).remove();
                        })
                        .catch(omegaup.UI.apiError);
                    };
                  })(response.problems[i].alias),
                ),
              ),
          );
        }
      })
      .catch(omegaup.UI.apiError);
  }

  omegaup.API.Problem.list()
    .then(function(problems) {
      // Got the problems, lets populate the dropdown with them
      for (var i = 0; i < problems.results.length; i++) {
        problem = problems.results[i];
        $('select#problems').append(
          $('<option></option>')
            .attr('value', problem.alias)
            .text(problem.title),
        );
      }
    })
    .catch(omegaup.UI.apiError);

  omegaup.UI.problemTypeahead($('#problems-dropdown'));

  // Edit users
  omegaup.UI.userTypeahead($('#username-admin'));
  omegaup.UI.userTypeahead($('#usernameOrEmail'));
  omegaup.UI.typeahead($('#groupalias-admin'), omegaup.API.Group.list);

  $('#add-admin-form').on('submit', function() {
    omegaup.API.Contest.addAdmin({
      contest_alias: interviewAlias,
      usernameOrEmail: $('#username-admin').val(),
    })
      .then(function(response) {
        omegaup.UI.success(omegaup.T.adminAdded);
        $('div.post.footer').show();

        refreshContestAdmins();
      })
      .catch(omegaup.UI.apiError);

    return false; // Prevent refresh
  });

  // Add admin
  function refreshContestAdmins() {
    omegaup.API.Contest.admins({ contest_alias: interviewAlias })
      .then(function(admins) {
        $('#contest-admins').empty();
        // Got the contests, lets populate the dropdown with them
        for (var i = 0; i < admins.admins.length; i++) {
          var admin = admins.admins[i];
          $('#contest-admins').append(
            $('<tr></tr>')
              .append(
                $('<td></td>').append(
                  $('<a></a>')
                    .attr('href', '/profile/' + admin.username + '/')
                    .text(admin.username),
                ),
              )
              .append($('<td></td>').text(admin.role))
              .append(
                admin.role != 'admin'
                  ? $('<td></td>')
                  : $(
                      '<td><button type="button" class="close">' +
                        '&times;</button></td>',
                    ).on(
                      'click',
                      (function(username) {
                        return function(e) {
                          omegaup.API.Contest.removeAdmin({
                            contest_alias: interviewAlias,
                            usernameOrEmail: username,
                          })
                            .then(function(response) {
                              omegaup.UI.success(omegaup.T.adminRemoved);
                              $('div.post.footer').show();
                              var tr = e.target.parentElement.parentElement;
                              $(tr).remove();
                            })
                            .catch(omegaup.UI.apiError);
                        };
                      })(admin.username),
                    ),
              ),
          );
        }
        $('#contest-group-admins').empty();
        for (var i = 0; i < admins.group_admins.length; i++) {
          var group_admin = admins.group_admins[i];
          $('#contest-group-admins').append(
            $('<tr></tr>')
              .append(
                $('<td></td>').append(
                  $('<a></a>')
                    .attr('href', '/group/' + group_admin.alias + '/edit/')
                    .text(group_admin.name),
                ),
              )
              .append($('<td></td>').text(group_admin.role))
              .append(
                group_admin.role != 'admin'
                  ? $('<td></td>')
                  : $(
                      '<td><button type="button" class="close">' +
                        '&times;</button></td>',
                    ).on(
                      'click',
                      (function(alias) {
                        return function(e) {
                          omegaup.API.Contest.removeGroupAdmin({
                            contest_alias: interviewAlias,
                            group: alias,
                          })
                            .then(function(response) {
                              omegaup.UI.success(omegaup.T.adminRemoved);
                              $('div.post.footer').show();
                              var tr = e.target.parentElement.parentElement;
                              $(tr).remove();
                            })
                            .catch(omegaup.UI.apiError);
                        };
                      })(group_admin.alias),
                    ),
              ),
          );
        }
      })
      .catch(omegaup.UI.apiError);
  }
  $('#add-group-admin-form').on('submit', function() {
    omegaup.API.Contest.addGroupAdmin({
      contest_alias: interviewAlias,
      group: $('#groupalias-admin').val(),
    })
      .then(function(response) {
        omegaup.UI.success(omegaup.T.adminAdded);
        $('div.post.footer').show();
        refreshContestAdmins();
      })
      .catch(omegaup.UI.apiError);

    return false; // Prevent refresh
  });

  refreshContestProblems();
  refreshContestAdmins();
  fillCandidatesTable();
});
