omegaup.OmegaUp.on('ready', function() {
  var requiredFields = ['#source', '#title'];
  if (window.location.pathname.indexOf('/problem/new') !== 0) {
    requiredFields.push('#update-message');
  } else {
    requiredFields.push('#problem_contents');
  }
  requiredFields.each(addRemoveErrorClass);

  omegaup.API.Tag.list({query: ''})
      .then(function(response) {
        response.forEach(function(e) {
          $('#problem-form .tag-list')
              .append($('<a></a>')
                          .attr('href', '#tags')
                          .addClass('tag')
                          .addClass('pull-left')
                          .text(e.name)
                          .on('click', onTabClicked));
        });
      })
      .fail(omegaup.UI.apiError);

  function onTabClicked() {
    var tagname = $(this).text();
    var public = $('#tag-public').val() == 'true';
    $(this).remove();
    refreshProblemTags(tagname, public);
    return false;  // Prevent refresh
  }

  function refreshProblemTags(tagname, public) {
    $('#problem-tags')
        .append(
            $('<tr></tr>')
                .append($('<td class="tag-name"></td>')
                            .append($('<a></a>')
                                        .attr('href',
                                              '/problem/?tag[]=' +
                                                  encodeURIComponent(tagname))
                                        .text(tagname)))
                .append($('<td></td>').addClass('is-public').text(public))
                .append(
                    $('<td></td>')
                        .append(
                            '<button type="button" class="close">&times;</button>')
                        .on('click', function(e) {
                          var tr = e.target.parentElement.parentElement;
                          $('#problem-form .tag-list')
                              .append($('<a></a>')
                                          .attr('href', '#tags')
                                          .addClass('tag')
                                          .addClass('pull-left')
                                          .text(tagname)
                                          .on('click', onTabClicked));
                          $(tr).remove();
                        })));
  }

  $('#problem-form')
      .on('submit', function() {
        $('.has-error').removeClass('has-error');
        var errors = false;
        requiredFields.each(function(inputId) {
          var input = $(inputId);
          if (input.val() == '') {
            omegaup.UI.error(omegaup.T.editFieldRequired);
            input.parent().addClass('has-error');
            errors = true;
          }
        });

        if (errors) {
          return false;
        }
        var visibilityFields = $('input[name=visibility]', this);
        if (visibilityFields.attr('disabled')) {
          // Clear field name to prevent it from being submitted with
          // the rest of the form.
          visibilityFields.attr('name', '');
        }
        var selectedTags = [];
        $('#problem-tags tr')
            .each(function(index) {
              selectedTags.push({
                tagname: $(this).find('td.tag-name a').text(),
                public: $(this).find('td.is-public').text() == 'true',
              });
            });
        $('input[name=selected_tags]').val(JSON.stringify(selectedTags));
      });

  function addRemoveErrorClass(inputId) {
    var input = $(inputId);
    input.on('input', function() {
      if (input.val() == '') {
        input.parent().addClass('has-error');
      } else {
        input.parent().removeClass('has-error');
      }
    });
  }
});
