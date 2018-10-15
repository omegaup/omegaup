function generateAlias(title, aliasLength) {
  // Remove accents
  title = title.latinize();

  // Replace whitespace
  title = title.replace(/\s+/g, '-');

  // Remove invalid characters
  title = title.replace(/[^a-zA-Z0-9_-]/g, '');

  title = title.substring(0, aliasLength);

  return title;
}

omegaup.OmegaUp.on('ready', function() {
  var formData = $('#form-data');
  var formName = formData.attr('data-name');
  var existsFn = null;
  var aliasLength = 0;

  function onAliasExists(data) {
    if (!data.exists) {
      onAliasReady();
      return;
    }
    omegaup.UI.error(
        omegaup.UI.formatString(omegaup.T.aliasAlreadyInUse,
                                {alias: omegaup.UI.escape($('#alias').val())}));
    $('#alias').trigger('focus');
  }

  function onAliasReady() { omegaup.UI.dismissNotifications(); }

  switch (formName) {
    case 'problems':
      existsFn = function(alias) {
        omegaup.API.Problem.details({problem_alias: alias})
            .then(onAliasExists)
            .fail(omegaup.UI.apiError);
      };
      aliasLength = 32;
      break;

    case 'groups':
      existsFn = function(alias) {
        omegaup.API.Group.details({group_alias: alias})
            .then(onAliasExists)
            .fail(omegaup.UI.apiError);
      };
      aliasLength = 50;
      break;

    case 'interviews':
      existsFn = function(alias) {
        omegaup.API.Interview.details({interview_alias: alias})
            .then(onAliasExists)
            .fail(omegaup.UI.apiError);
      };
      aliasLength = 32;
      break;
  }

  $('#title')
      .on('blur', function() {
        $('#alias')
            .val(generateAlias($(this).val(), aliasLength))
            .trigger('change');
      });

  $('#alias').on('change', function() { existsFn($('#alias').val()); });
});
