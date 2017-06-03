omegaup.OmegaUp.on('ready', function() {
  var requiredFields = ['#source', '#title'];
  if (window.location.pathname.indexOf('/problem/new') !== 0) {
    requiredFields.push('#update-message');
  } else {
    requiredFields.push('#problem_contents');
  }
  requiredFields.each(addRemoveErrorClass);

  $('#problem-form')
      .submit(function() {
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
        let visibilityFields = $('input[name=visibility]', this);
        if (visibilityFields.attr('disabled')) {
          // Clear field name to prevent it from being submitted with
          // the rest of the form.
          visibilityFields.attr('name', '');
        }
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
