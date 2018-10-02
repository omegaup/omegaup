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
        var tags = {};
        $('#problem-tags a')
            .each(function(index) { tags[$(this).text()] = true; });
        response.forEach(function(e) {
          $('.tag-list')
              .append($('<a></a>')
                          .attr('href', '#tags')
                          .addClass('tag')
                          .addClass('pull-left')
                          .text(e.name));
        });
        $(document)
            .on('click', '.tag', function(event) {
              var tagname = $(this).text();
              var public = $('#tag-public').val() == 'true';
              $(this).remove();
              $('div.post.footer').show();
              refreshProblemTags(tagname, public);
              return false;  // Prevent refresh
            });
      })
      .fail(omegaup.UI.apiError);

  function refreshProblemTags(tagname, public) {
    $('#problem-tags')
        .append(
            $('<tr></tr>')
                .append(
                    $('<td class="tag-name"></td>')
                        .append($('<a></a>')
                                    .attr('href', '/problem/?tag[]=' + tagname)
                                    .text(tagname)))
                .append($('<td class="is-public"></td>').text(public))
                .append($('<td><button type="button" class="close">' +
                          '&times;</button></td>')
                            .on('click', (function(tagname) {
                                  return function(e) {
                                    $('div.post.footer').show();
                                    var tr =
                                        e.target.parentElement.parentElement;
                                    $('.tag-list')
                                        .append('<a href="#tags" ' +
                                                'class="tag pull-left">' +
                                                $(tr).find('a').text() +
                                                '</a>');
                                    $(tr).remove();
                                  };
                                })(tagname))));
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
                tagname: $(this).find('td.tag-name').find('a').text(),
                public: $(this).find('td.is-public').text(),
              });
            });
        $('#selected-tags').val(JSON.stringify(selectedTags));
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
