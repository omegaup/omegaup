$('#nav-contests ul li a[href="/group/"]').parent().addClass('active');

var formData = $('#form-data');
var formName = formData.attr('data-name');
var formPage = formData.attr('data-page');
var formAlias = formData.attr('data-alias');

$(function() {
  if (formPage === 'new') {
    $('.new-group-form')
        .on('submit', function() {
          omegaup.API.Group.create({
                             alias: $('.new-group-form #alias').val(),
                             name: $('.new-group-form #title').val(),
                             description:
                                 $('.new-group-form #description').val(),
                           })
              .then(function(data) {
                window.location.replace('/group/' +
                                        $('.new-group-form #alias').val() +
                                        '/edit/#members');
              })
              .fail(omegaup.UI.apiError);

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

    $('#add-scoreboard-form')
        .on('submit', function() {
          omegaup.API.Group.createScoreboard({
                             group_alias: groupAlias,
                             alias: $('#alias').val(),
                             name: $('#title').val(),
                             description: $('#description').val(),
                           })
              .then(function(response) {
                omegaup.UI.success(omegaup.T.groupEditScoreboardsAdded);
                $('div.post.footer').show();
                refreshGroupScoreboards();
              })
              .fail(omegaup.UI.apiError);

          return false;  // Prevent refresh
        });

    function refreshGroupScoreboards() {
      omegaup.API.Group.details({group_alias: groupAlias})
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
                          .append($('<td>' +
                                    '<a class="glyphicon glyphicon-edit" ' +
                                    'href="/group/' + groupAlias +
                                    '/scoreboard/' + scoreboard.alias +
                                    '/edit/" ' +
                                    'title="Edit"></a></td>')));
            }
          })
          .fail(omegaup.UI.apiError);
    }

    refreshGroupScoreboards();
  }
});
