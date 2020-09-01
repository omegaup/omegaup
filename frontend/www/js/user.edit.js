omegaup.OmegaUp.on('ready', function () {
  var original_locale = null;
  var original_school = null;
  var original_school_id = null;
  $('#birth_date')
    .datepicker()
    .on('changeDate', function (e) {
      $(this).data('date', e.date);
    });
  $('#graduation_date')
    .datepicker()
    .on('changeDate', function (e) {
      $(this).data('date', e.date);
    });
  $('#school_id').val('');
  omegaup.Typeahead.schoolTypeahead($('#school'), function (item, val, text) {
    $('#school_id').val(val.id);
    $('#school_name').val(val.label);
  });

  $('#school').on('change', function () {
    if ($('#school').val() === '' || original_school !== $('#school').val()) {
      $('#school_id').val('');
    }
  });

  $('#country_id').on('change', function () {
    // Clear select
    $('#state_id option').remove();
    $('#state_id').val('');

    var country = iso3166.country($('#country_id').val() || '');

    if (!country || !country.sub) {
      $('#state_id').attr('disabled', 'disabled');
      return;
    }
    $('#state_id').prop('disabled', false);

    var subdivisions = Object.keys(country.sub).map(function (code) {
      return { code: code, name: country.sub[code].name };
    });

    subdivisions.sort(function (a, b) {
      return Intl.Collator().compare(a.name, b.name);
    });

    subdivisions.forEach(function (subdivision) {
      var id = subdivision.code.split('-')[1];
      $('#state_id').append(
        $('<option></option').attr('value', id).text(subdivision.name),
      );
    });
  });

  omegaup.API.User.profile()
    .then(function (data) {
      $('#username').text(data.username);
      $('#username').val(data.username);
      $('#name').val(data.name);
      $('#birth_date')
        .data('date', data.birth_date)
        .val(
          data.birth_date
            ? omegaup.Time.formatDate(data.birth_date)
            : data.birth_date,
        );
      $('#gender').val(data.gender);
      $('#graduation_date')
        .data('date', data.graduation_date)
        .val(
          data.graduation_date
            ? omegaup.Time.formatDate(data.graduation_date)
            : data.graduation_date,
        );
      $('#country_id').val(data.country_id);
      $('#locale').val(data.locale);

      // Update state dropdown status
      $('#country_id').trigger('change');

      $('#state_id').val(data.state_id);
      $('#scholar_degree').val(data.scholar_degree);
      $('#school_id').val(data.school_id);
      $('#school').val(data.school);
      $('#programming_language').val(data.preferred_language);
      $('#is_private').prop('checked', data.is_private == 1);
      $('#hide_problem_tags').prop('checked', data.hide_problem_tags == 1);

      original_locale = data.locale;
      original_school = data.school;
      original_school_id = data.school_id;
    })
    .catch(omegaup.UI.apiError);

  $('form#user_profile_form').on('submit', function (ev) {
    ev.preventDefault();
    var birth_date = new Date($('#birth_date').data('date'));
    birth_date.setHours(23);

    var graduation_date = new Date($('#graduation_date').data('date'));
    graduation_date.setHours(23);

    var locale_changed = original_locale != $('#locale').val();

    if (
      $('#school_id').val() == original_school_id &&
      $('#school').val() != original_school
    ) {
      $('#school_id').val('');
    }

    if ($('#name').val().length > 50) {
      omegaup.UI.error(omegaup.T.userEditNameTooLong);
      return;
    }

    var user = {
      username: $('#username').val(),
      name: $('#name').val(),
      birth_date: birth_date.getTime() / 1000,
      gender: $('#gender').val(),
      country_id: $('#country_id').val() || undefined,
      state_id: $('#state_id').val() || undefined,
      scholar_degree: $('#scholar_degree').val(),
      graduation_date: graduation_date.getTime() / 1000,
      school_name: $('#school').val(),
      locale: $('#locale').val(),
      preferred_language: $('#programming_language').val() || undefined,
      is_private: $('#is_private').prop('checked'),
      hide_problem_tags: $('#hide_problem_tags').prop('checked'),
    };

    if ($('#school_id').val() !== '') {
      user.school_id = $('#school_id').val();
    }

    omegaup.API.User.update(user)
      .then(function (response) {
        if (locale_changed) {
          window.location.reload();
        } else {
          omegaup.UI.success(omegaup.T.userEditSuccess);
        }
      })
      .catch(omegaup.UI.apiError);
  });

  $('form#change-password-form').on('submit', function (ev) {
    ev.preventDefault();
    var newPassword = $('#new-password-1').val();
    var newPassword2 = $('#new-password-2').val();
    if (newPassword != newPassword2) {
      omegaup.UI.error(omegaup.T.passwordMismatch);
      return;
    }

    omegaup.API.User.changePassword({
      old_password: $('#old-password').val(),
      password: newPassword,
    })
      .then(function () {
        omegaup.UI.success(omegaup.T.passwordResetResetSuccess);
      })
      .catch(omegaup.UI.apiError);
  });
});
