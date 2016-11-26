$('.navbar #nav-contests').addClass('active');

var formData = $('#form-data');
var formName = formData.attr('data-name');
var formPage = formData.attr('data-page');
var formAlias = formData.attr('data-alias');

$(function() {
  if (formPage === 'list') {
    function fillGroupsList() {
      omegaup.API.getMyGroups(function(groups) {
        var html = '';

        for (var i = 0; i < groups.groups.length; i++) {
          html += '<tr>' + "<td><b><a href='/group/" + groups.groups[i].alias +
                  "/edit/#scoreboards'>" +
                  omegaup.UI.escape(groups.groups[i].name) + '</a></b></td>' +
                  '<td><a class="glyphicon glyphicon-edit" href="/group/' +
                  groups.groups[i].alias +
                  '/edit#edit" title="{#wordsEdit#}"></a></td>' +
                  '</tr>';
        }

        $('#groups_list').removeClass('wait_for_ajax');
        $('#groups_list > table > tbody').empty().html(html);
      });
    }

    fillGroupsList();
  } else if (formPage === 'new') {
    $('.new_group_form')
        .submit(function() {
          omegaup.API.createGroup(
              $('.new_group_form #alias').val(),
              $('.new_group_form #title').val(),
              $('.new_group_form #description').val(), function(data) {
                if (data.status === 'ok') {
                  window.location.replace('/group/' +
                                          $('.new_group_form #alias').val() +
                                          '/edit/#members');
                } else {
                  omegaup.UI.error(data.error || 'error');
                }
              });

          return false;
        });
  } else if (formPage === 'edit') {
    var groupAlias = formAlias;

    // Sections UI actions
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

    // Typehead
    refreshGroupMembers();
    omegaup.UI.userTypeahead($('#member-username'));

    $('#add-member-form')
        .submit(function() {
          var username = $('#member-username').val();

          omegaup.API.addUserToGroup(groupAlias, username, function(response) {
            if (response.status === 'ok') {
              omegaup.UI.success('Member successfully added!');
              $('div.post.footer').show();

              refreshGroupMembers();
            } else {
              omegaup.UI.error(response.error || 'error');
            }
          });

          return false;  // Prevent refresh
        });

    function refreshGroupMembers() {
      omegaup.API.getGroupMembers(groupAlias, function(group) {
        $('#group-members').empty();

        for (var i = 0; i < group.users.length; i++) {
          var user = group.users[i];
          $('#group-members')
              .append(
                  $('<tr></tr>')
                      .append(
                          $('<td></td>')
                              .append(
                                  $('<a></a>')
                                      .attr('href',
                                            '/profile/' + user.username + '/')
                                      .text(omegaup.UI.escape(user.username))))
                      .append(
                          $('<td><button type="button" class="close">' +
                            '&times;</button></td>')
                              .click((function(username) {
                                return function(e) {
                                  omegaup.API.removeUserFromGroup(
                                      groupAlias, username, function(response) {
                                        if (response.status === 'ok') {
                                          omegaup.UI.success(
                                              'Member successfully removed!');
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
                              })(user.username))));
        }
      });
    }

    $('#add-scoreboard-form')
        .submit(function() {
          var name = $('#title').val();
          var alias = $('#alias').val();
          var description = $('#description').val();

          omegaup.API.addScoreboardToGroup(
              groupAlias, alias, name, description, function(response) {
                if (response.status === 'ok') {
                  omegaup.UI.success('Scoreboard successfully added!');
                  $('div.post.footer').show();

                  refreshGroupScoreboards();
                } else {
                  omegaup.UI.error(response.error || 'error');
                }
              });

          return false;  // Prevent refresh
        });

    function refreshGroupScoreboards() {
      omegaup.API.getGroup(groupAlias, function(group) {
        $('#group-scoreboards').empty();

        for (var i = 0; i < group.scoreboards.length; i++) {
          var scoreboard = group.scoreboards[i];
          $('#group-scoreboards')
              .append(
                  $('<tr></tr>')
                      .append($('<td></td>')
                                  .append($('<a></a>')
                                              .attr('href',
                                                    '/group/' + groupAlias +
                                                        '/scoreboard/' +
                                                        scoreboard.alias + '/')
                                              .text(omegaup.UI.escape(
                                                  scoreboard.name))))
                      .append($(
                          '<td>' +
                          '<a class="glyphicon glyphicon-edit" href="/group/' +
                          groupAlias + '/scoreboard/' + scoreboard.alias +
                          '/edit/" title="Edit"></a></td>')));
        }
      });
    }

    refreshGroupScoreboards();
  }
});
