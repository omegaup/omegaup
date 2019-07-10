omegaup.OmegaUp.on('ready', function() {
  var original_locale = null;
  var original_school = null;
  var original_school_id = null;
  $('#birth_date').datepicker();
  $('#graduation_date').datepicker();

  $('#school_id').val('');
  omegaup.UI.schoolTypeahead($('#school'), function(item, val, text) {
    $('#school_id').val(val.id);
    $('#school_name').val(val.label);
  });

  $('#country_id')
      .on('change', function() {
        // Clear select
        $('#state_id option').remove();
        $('#state_id').val('');

        var country = iso3166.country($('#country_id').val() || '');

        if (!country || !country.sub) {
          $('#state_id').attr('disabled', 'disabled');
          return;
        }
        $('#state_id').prop('disabled', false);

        var subdivisions =
            Object.keys(country.sub)
                .map(function(code) {
                  return {code: code, name: country.sub[code].name};
                });

        subdivisions.sort(function(a, b) {
          return Intl.Collator().compare(a.name, b.name);
        });

        subdivisions.forEach(function(subdivision) {
          var id = subdivision.code.split('-')[1];
          $('#state_id')
              .append($('<option></option')
                          .attr('value', id)
                          .text(subdivision.name));
        });
      });

  omegaup.API.User.profile()
      .then(function(data) {
        $('#username').text(data.userinfo.username);
        $('#username').val(data.userinfo.username);
        $('#name').val(data.userinfo.name);
        $('#birth_date').val(omegaup.UI.formatDate(data.userinfo.birth_date));
        $('#gender').val(data.userinfo.gender);
        $('#graduation_date')
            .val(omegaup.UI.formatDate(data.userinfo.graduation_date));
        $('#country_id').val(data.userinfo.country_id);
        $('#locale').val(data.userinfo.locale);

        // Update state dropdown status
        $('#country_id').trigger('change');

        $('#state_id').val(data.userinfo.state_id);
        $('#scholar_degree').val(data.userinfo.scholar_degree);
        $('#school_id').val(data.userinfo.school_id);
        $('#school').val(data.userinfo.school);
        $('#programming_language').val(data.userinfo.preferred_language);
        $('#is_private').prop('checked', data.userinfo.is_private == 1);
        $('#hide_problem_tags')
            .prop('checked', data.userinfo.hide_problem_tags == 1);

        original_locale = data.userinfo.locale;
        original_school = data.userinfo.school;
        original_school_id = data.userinfo.school_id;
      })
      .fail(omegaup.UI.apiError);

  $('form#user_profile_form')
      .on('submit', function(ev) {
        ev.preventDefault();
        var birth_date = new Date($('#birth_date').val());
        birth_date.setHours(23);

        var graduation_date = new Date($('#graduation_date').val());
        graduation_date.setHours(23);

        var locale_changed = original_locale != $('#locale').val();

        if ($('#school_id').val() == original_school_id &&
            $('#school').val() != original_school) {
          $('#school_id').val('');
        }

        if ($('#name').val().length > 50) {
          omegaup.UI.error(omegaup.T.userEditNameTooLong);
          return;
        }

        omegaup.API.User.update({
                          username: $('#username').val(),
                          name: $('#name').val(),
                          birth_date: birth_date.getTime() / 1000,
                          gender: $('#gender').val(),
                          country_id: $('#country_id').val() || undefined,
                          state_id: $('#state_id').val() || undefined,
                          scholar_degree: $('#scholar_degree').val(),
                          graduation_date: graduation_date.getTime() / 1000,
                          school_id: $('#school_id').val(),
                          school_name: $('#school').val(),
                          locale: $('#locale').val(),
                          preferred_language: $('#programming_language').val(),
                          is_private: $('#is_private').prop('checked') ? 1 : 0,
                          hide_problem_tags:
                              $('#hide_problem_tags').prop('checked') ? 1 : 0
                        })
            .then(function(response) {
              if (locale_changed) {
                window.location.reload();
              } else {
                omegaup.UI.success(omegaup.T.userEditSuccess);
              }
            })
            .fail(omegaup.UI.apiError);
      });

  $('form#change-password-form')
      .on('submit', function(ev) {
        ev.preventDefault();
        var newPassword = $('#new-password-1').val();
        var newPassword2 = $('#new-password-2').val();
        if (newPassword != newPassword2) {
          omegaup.UI.error(omegaup.T.passwordMismatch);
          return;
        }

        omegaup.API.User.changePassword({
                          old_password: $('#old-password').val(),
                          password: newPassword
                        })
            .then(function() {
              omegaup.UI.success(omegaup.T.passwordResetResetSuccess);
            })
            .fail(omegaup.UI.apiError);
      });
});
