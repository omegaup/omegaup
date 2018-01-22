omegaup.OmegaUp.on('ready', function() {
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

  var contestAlias =
      /\/contest\/([^\/]+)\/edit\/?.*/.exec(window.location.pathname)[1];

  omegaup.API.Contest.adminDetails({contest_alias: contestAlias})
      .then(function(contest) {
        $('.page-header h1 span')
            .html(omegaup.T.contestEdit + ' ' + contest.title);
        $('.page-header h1 small')
            .html('&ndash; <a href="/arena/' + contestAlias + '/">' +
                  omegaup.T.contestDetailsGoToContest + '</a>');
        $('.new_contest_form #title').val(contest.title);
        $('.new_contest_form #alias').val(contest.alias);
        $('.new_contest_form #description').val(contest.description);
        $('.new_contest_form #start-time')
            .val(omegaup.UI.formatDateTime(contest.start_time));
        $('.new_contest_form #finish-time')
            .val(omegaup.UI.formatDateTime(contest.finish_time));

        if (contest.window_length === null) {
          // Disable window length
          $('#window-length-enabled').removeAttr('checked');
          $('#window-length').val('');
        } else {
          $('#window-length-enabled').attr('checked', 'checked');
          $('#window-length').removeAttr('disabled');
          $('#window-length').val(contest.window_length);
        }

        $('.new_contest_form #points-decay-factor')
            .val(contest.points_decay_factor);
        $('.new_contest_form #submissions-gap')
            .val(contest.submissions_gap / 60);
        $('.new_contest_form #feedback').val(contest.feedback);
        $('.new_contest_form #penalty').val(contest.penalty);
        $('.new_contest_form #public').val(contest.public);
        $('.new_contest_form #register').val(contest.contestant_must_register);
        $('.new_contest_form #scoreboard').val(contest.scoreboard);
        $('.new_contest_form #penalty-type').val(contest.penalty_type);
        $('.new_contest_form #show-scoreboard-after')
            .val(contest.show_scoreboard_after);
        contest.languages.each(function(lang) {
          $('option[value="' + lang + '"]').prop('selected', true);
        });
        $('.new_contest_form #languages').multiselect('refresh');

        $('.new_contest_form #basic-information-required')
            .prop('checked', contest.needs_basic_information);

        $('.contest-publish-form #public').val(contest.public);

        if (contest.contestant_must_register == null ||
            contest.contestant_must_register == '0') {
          $('#requests').hide();
        }
      })
      .fail(omegaup.UI.apiError);

  omegaup.API.Problem.list()
      .then(function(problems) {
        // Got the problems, lets populate the dropdown with them
        for (var i = 0; i < problems.results.length; i++) {
          problem = problems.results[i];
          $('select#problems')
              .append($('<option></option>')
                          .attr('value', problem.alias)
                          .text(problem.title));
        }
      })
      .fail(omegaup.UI.apiError);

  refreshContestProblems();
  refreshContestContestants();
  refreshContestAdmins();
  refreshContestRequests();

  // Edit contest
  $('.new_contest_form')
      .on('submit', function() {
        return updateContest($('.new_contest_form #public').val());
      });

  // Publish
  $('.contest-publish-form')
      .on('submit', function() {
        return updateContest($('.contest-publish-form #public').val());
      });

  // Update contest
  function updateContest(public) {
    var window_length_value = $('#window-length-enabled').is(':checked') ?
                                  $('#window-length').val() :
                                  'NULL';

    omegaup.API.Contest
        .update({
          contest_alias: contestAlias,
          title: $('#title').val(),
          description: $('#description').val(),
          start_time: (new Date($('#start-time').val()).getTime()) / 1000,
          finish_time: (new Date($('#finish-time').val()).getTime()) / 1000,
          window_length: window_length_value,
          alias: $('#alias').val(),
          points_decay_factor: $('#points-decay-factor').val(),
          submissions_gap: $('#submissions-gap').val() * 60,
          feedback: $('#feedback').val(),
          penalty: $('#penalty').val(), public: public,
          scoreboard: $('#scoreboard').val(),
          penalty_type: $('#penalty-type').val(),
          show_scoreboard_after: $('#show-scoreboard-after').val(),
          languages: $('#languages').val(),
          contestant_must_register: $('#register').val(),
          basic_information:
              $('#basic-information-required').is(':checked') ? '1' : '0',
        })
        .then(function(data) {
          if (data.status == 'ok') {
            omegaup.UI.success(omegaup.T.contestEditContestEdited +
                               ' <a href="/arena/' +
                               $('.new_contest_form #alias').val() + '">' +
                               omegaup.T.contestEditGoToContest + '</a>');
            $('div.post.footer').show();
            window.scrollTo(0, 0);
          } else {
            omegaup.UI.error(data.error || 'error');
          }
        })
        .fail(omegaup.UI.apiError);
    return false;
  }

  // Edit problems
  function refreshContestProblems() {
    omegaup.API.Contest.problems({contest_alias: contestAlias})
        .then(function(response) {
          var problems = $('#contest-problems');
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
                            .on('click', (function(problem) {
                                  return function(e) {
                                    omegaup.API.Contest.removeProblem({
                                                         contest_alias:
                                                             contestAlias,
                                                         problem_alias: problem,
                                                       })
                                        .then(function(response) {
                                          omegaup.UI.success(
                                              'Problem successfully removed!');
                                          $('div.post.footer').show();
                                          $(e.target.parentElement
                                                .parentElement)
                                              .remove();
                                        })
                                        .fail(omegaup.UI.apiError);
                                  };
                                })(response.problems[i].alias))));
          }
        })
        .fail(omegaup.UI.apiError);
  }

  $('#add-problem-form')
      .on('submit', function() {
        omegaup.API.Contest.addProblem({
                             contest_alias: contestAlias,
                             order_in_contest: $('input#order').val(),
                             problem_alias: $('input#problems-dropdown').val(),
                             points: $('input#points').val(),
                           })
            .then(function(response) {
              omegaup.UI.success('Problem successfully added!');
              $('div.post.footer').show();
              refreshContestProblems();
            })
            .fail(omegaup.UI.apiError);

        return false;  // Prevent page refresh
      });

  omegaup.UI.userTypeahead($('#username-contestant'));
  omegaup.UI.userTypeahead($('#username-admin'));
  omegaup.UI.typeahead($('#groupalias-admin'), omegaup.API.Group.list);
  omegaup.UI.problemTypeahead($('#problems-dropdown'));

  function refreshContestRequests() {
    $('#user-requests-table')
        .bootstrapTable({
          method: 'get',
          url: '/api/contest/requests/contest_alias/' + contestAlias + '/',
          onPostBody: function() {
            $('.close.request-accept')
                .on('click', (function() {
                      return function() {
                        var username = $(this).val();
                        omegaup.API.Contest.arbitrateRequest({
                                             contest_alias: contestAlias,
                                             username: username,
                                             resolution: true /* accepted */,
                                             note: '',
                                           })
                            .then(function(response) {
                              omegaup.UI.success(omegaup.T.successfulOperation);
                              $('#user-requests-table')
                                  .bootstrapTable('refresh');
                            })
                            .fail(omegaup.UI.apiError);
                      };
                    })());

            $('.close.request-deny')
                .on('click', (function() {
                      return function() {
                        var username = $(this).val();
                        omegaup.API.Contest.arbitrateRequest({
                                             contest_alias: contestAlias,
                                             username: username,
                                             resolution: false /* rejected */,
                                             note: '',
                                           })
                            .then(function(response) {
                              omegaup.UI.success(omegaup.T.successfulOperation);
                              $('#user-requests-table')
                                  .bootstrapTable('refresh');
                            })
                            .fail(omegaup.UI.apiError);
                      };
                    })());
          },
          responseHandler: function(res) { return res.users; },
          columns: [
            {field: 'username'},
            {field: 'country', sortable: true},
            {field: 'request_time'},
            {
              field: 'accepted',
              sortable: true,
              formatter: function(value) {
                if (value == null) {
                  return omegaup.T.wordsDenied;
                }

                if (value == 'true' || value == '1') {
                  return omegaup.T.wordAccepted;
                }

                return omegaup.T.wordsDenied;
              }
            },
            {
              field: 'last_update',
              formatter: function(v, o) {
                return v + ' (' + o.admin.username + ')';
              }
            },
            {
              field: 'accepted',
              formatter: function(a, b, c) {
                return '<button type="button" ' +
                       'class="close request-deny" value="' + b.username +
                       '" style="color:red">&times;</button>' +
                       '<button type="button" ' +
                       'class="close request-accept" value="' + b.username +
                       '" style="color:green">&#x2713;</button>';
              }
            }
          ]
        });
  }

  function refreshContestContestants() {
    omegaup.API.Contest.users({contest_alias: contestAlias})
        .then(function(users) {
          $('#contest-users').empty();
          // Got the contests, lets populate the dropdown with them
          for (var i = 0; i < users.users.length; i++) {
            user = users.users[i];
            $('#contest-users')
                .append(
                    $('<tr></tr>')
                        .append($('<td></td>')
                                    .append($('<a></a>')
                                                .attr('href',
                                                      '/profile/' +
                                                          user.username + '/')
                                                .text(user.username)
                                                .append(omegaup.UI.getFlag(
                                                    user['country_id']))))
                        .append($('<td></td>').text(user.access_time))
                        .append(
                            $('<td><button type="button" class="close">' +
                              '&times;</button></td>')
                                .on('click', (function(username) {
                                      return function(e) {
                                        omegaup.API.Contest.removeUser({
                                                             contest_alias:
                                                                 contestAlias,
                                                             usernameOrEmail:
                                                                 username,
                                                           })
                                            .then(function(response) {
                                              omegaup.UI.success(
                                                  omegaup.T.userRemoveSuccess);
                                              $('div.post.footer').show();
                                              var tr = e.target.parentElement
                                                           .parentElement;
                                              $(tr).remove();
                                            })
                                            .fail(omegaup.UI.apiError);
                                      };
                                    })(user.username))));
          }
        })
        .fail(omegaup.UI.apiError);
  }

  $('#add-contestant-form')
      .on('submit', function(evt) {
        evt.preventDefault;
        isBulk = $($(this).context.attributes[0].ownerDocument.activeElement)
                     .hasClass('user-add-bulk');
        if (isBulk) {
          var promises = $('textarea[name="usernames"]')
                             .val()
                             .split(',')
                             .map(function(username) {
                               return omegaup.API.Contest.addUser({
                                 contest_alias: contestAlias,
                                 usernameOrEmail: $.trim(username),
                               });
                             });
          $.when.apply(promises)
              .then(function() {
                omegaup.UI.success(omegaup.T.bulkUserAddSuccess);
              })
              .fail(function() {
                omegaup.UI.error(omegaup.T.bulkUserAddError);
              })
              .always(refreshContestContestants);
          return false;
        } else {
          var username = $('#username-contestant').val();
          omegaup.API.Contest.addUser({
                               contest_alias: contestAlias,
                               usernameOrEmail: username,
                             })
              .then(function(response) {
                omegaup.UI.success(omegaup.T.successfulAddUser);
                $('div.post.footer').show();
                refreshContestContestants();
              })
              .fail(omegaup.UI.apiError);
          return false;
        }
      });

  // Add admin
  function refreshContestAdmins() {
    omegaup.API.Contest.admins({contest_alias: contestAlias})
        .then(function(admins) {
          $('#contest-admins').empty();
          // Got the contests, lets populate the dropdown with them
          for (var i = 0; i < admins.admins.length; i++) {
            var admin = admins.admins[i];
            var siteAdmin = (admin.role == 'site-admin') ? admin.role : '';
            $('#contest-admins')
                .append(
                    $('<tr></tr>')
                        .addClass(siteAdmin)
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
                                    .on('click', (function(username) {
                                          return function(e) {
                                            omegaup.API.Contest
                                                .removeAdmin({
                                                  contest_alias: contestAlias,
                                                  usernameOrEmail: username,
                                                })
                                                .then(function(response) {
                                                  omegaup.UI.success(
                                                      omegaup.T.adminRemoved);
                                                  $('div.post.footer').show();
                                                  var tr =
                                                      e.target.parentElement
                                                          .parentElement;
                                                  $(tr).remove();
                                                })
                                                .fail(omegaup.UI.apiError);
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
                                    .on('click', (function(alias) {
                                          return function(e) {
                                            omegaup.API.Contest
                                                .removeGroupAdminFromContest({
                                                  contest_alias: contestAlias,
                                                  group: alias,
                                                })
                                                .then(function(response) {
                                                  omegaup.UI.success(
                                                      omegaup.T
                                                          .groupAdminRemoved);
                                                  $('div.post.footer').show();
                                                  var tr =
                                                      e.target.parentElement
                                                          .parentElement;
                                                  $(tr).remove();
                                                })
                                                .fail(omegaup.UI.apiError);
                                          };
                                        })(group_admin.alias))));
          }

          $('#contest-admins .site-admin').hide();
        })
        .fail(omegaup.UI.apiError);
  }

  $('#add-admin-form')
      .on('submit', function() {
        omegaup.API.Contest.addAdmin({
                             contest_alias: contestAlias,
                             usernameOrEmail: $('#username-admin').val(),
                           })
            .then(function(response) {
              omegaup.UI.success(omegaup.T.adminAdded);
              $('div.post.footer').show();
              refreshContestAdmins();
            })
            .fail(omegaup.UI.apiError);

        return false;  // Prevent refresh
      });

  $('#toggle-site-admins')
      .on('change', function() {
        if ($(this).is(':checked')) {
          $('#contest-admins .site-admin').show();
        } else {
          $('#contest-admins .site-admin').hide();
        }
      });

  $('#add-group-admin-form')
      .on('submit', function() {
        omegaup.API.Contest.addGroupAdmin({
                             contest_alias: contestAlias,
                             group: $('#groupalias-admin').val(),
                           })
            .then(function(response) {
              omegaup.UI.success(omegaup.T.groupAdminAdded);
              $('div.post.footer').show();
              refreshContestAdmins();
            })
            .fail(omegaup.UI.apiError);

        return false;  // Prevent refresh
      });
});
