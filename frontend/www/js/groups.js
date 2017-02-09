$('.navbar #nav-contests').addClass('active');

var formData = $('#form-data');
var formName = formData.attr('data-name');
var formPage = formData.attr('data-page');
var formAlias = formData.attr('data-alias');

$(function() {
  if (formPage === 'list') {
    function fillGroupsList() {
      omegaup.API.getMyGroups().then(function(groups) {
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
      }).fail(omegaup.UI.apiError);
    }

    fillGroupsList();
  } else if (formPage === 'new') {
    $('.new_group_form')
        .submit(function() {
          omegaup.API.createGroup({
                       alias: $('.new_group_form #alias').val(),
                       name: $('.new_group_form #title').val(),
                       description: $('.new_group_form #description').val(),
                     })
              .then(function(data) {
                window.location.replace('/group/' +
                                        $('.new_group_form #alias').val() +
                                        '/edit/#members');
              }).fail(omegaup.UI.apiError);

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

          omegaup.API.addUserToGroup({
                       group_alias: groupAlias,
                       usernameOrEmail: username,
                     })
              .then(function(response) {
                omegaup.UI.success('Member successfully added!');
                $('div.post.footer').show();

                refreshGroupMembers();
              }).fail(omegaup.UI.apiError);

          return false;  // Prevent refresh
        });

    function refreshGroupMembers() {
      omegaup.API.getGroupMembers({group_alias: groupAlias})
          .then(function(group) {
            $('#group-members').empty();

            for (var i = 0; i < group.users.length; i++) {
              var user = group.users[i];
              $('#group-members')
                  .append(
                      $('<tr></tr>')
                          .append($('<td></td>')
                                      .append($('<a></a>')
                                                  .attr('href',
                                                        '/profile/' +
                                                            user.username + '/')
                                                  .text(omegaup.UI.escape(
                                                      user.username))))
                          .append(
                              $('<td><button type="button" class="close">' +
                                '&times;</button></td>')
                                  .click((function(username) {
                                    return function(e) {
                                      omegaup.API.removeUserFromGroup({
                                                   group_alias: groupAlias,
                                                   usernameOrEmail: username,
                                                 })
                                          .then(function(response) {
                                            omegaup.UI.success(
                                                'Member successfully removed!');
                                            $('div.post.footer').show();
                                            var tr = e.target.parentElement
                                                         .parentElement;
                                            $(tr).remove();
                                          }).fail(omegaup.UI.apiError);
                                    };
                                  })(user.username))));
            }
          }).fail(omegaup.UI.apiError);
    }

    $('#add-scoreboard-form')
        .submit(function() {
          omegaup.API.addScoreboardToGroup({
                       group_alias: groupAlias,
                       alias: $('#alias').val(),
                       name: $('#title').val(),
                       description: $('#description').val(),
                     })
              .then(function(response) {
                omegaup.UI.success('Scoreboard successfully added!');
                $('div.post.footer').show();
                refreshGroupScoreboards();
              }).fail(omegaup.UI.apiError);

          return false;  // Prevent refresh
        });

    function refreshGroupScoreboards() {
      omegaup.API.getGroup({group_alias: groupAlias})
          .then(function(group) {
            $('#group-scoreboards').empty();

            for (var i = 0; i < group.scoreboards.length; i++) {
              var scoreboard = group.scoreboards[i];
              $('#group-scoreboards')
                  .append(
                      $('<tr></tr>')
                          .append(
                              $('<td></td>')
                                  .append($('<a></a>')
                                              .attr('href',
                                                    '/group/' + groupAlias +
                                                        '/scoreboard/' +
                                                        scoreboard.alias + '/')
                                              .text(omegaup.UI.escape(
                                                  scoreboard.name))))
                          .append($(
                              '<td>' +
                              '<a class="glyphicon glyphicon-edit" ' +
                              'href="/group/' + groupAlias + '/scoreboard/' +
                              scoreboard.alias + '/edit/" ' +
                              'title="Edit"></a></td>')));
            }
          }).fail(omegaup.UI.apiError);
    }

    refreshGroupScoreboards();
  }
});
