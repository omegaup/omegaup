$(document)
    .ready(function() {
      var original_locale = null;
      var original_school = null;
      var original_school_id = null;
      $('#birth_date').datepicker();
      $('#graduation_date').datepicker();

      $('#school_id').val('');
      $('#school')
          .typeahead(
              {
                minLength: 2,
                highlight: true,
              },
              {
                source: omegaup.UI.typeaheadWrapper(omegaup.API.searchSchools),
                displayKey: 'label',
                templates: {
                  empty: omegaup.T.schoolToBeAdded,
                }
              })
          .on('typeahead:selected', function(item, val, text) {
            $('#school_id').val(val.id);
            $('#school_name').val(val.label);
          });

      $('#country_id')
          .change(function() {
            // Clear select
            $('#state_id option')
                .each(function(index, option) { $(option)
               .remove(); });

            if ($('#country_id').val() == 'MX') {
              // Enable
              $('#state_id').removeAttr('disabled');

              $('#state_id')
                  .append($('<option></option>')
                              .attr('value', '52')
                              .text('Aguascalientes'));
              $('#state_id')
                  .append($('<option></option>')
                              .attr('value', '53')
                              .text('Baja California'));
              $('#state_id')
                  .append($('<option></option>')
                              .attr('value', '54')
                              .text('Baja California Sur'));
              $('#state_id')
                  .append($('<option></option>')
                              .attr('value', '55')
                              .text('Campeche'));
              $('#state_id')
                  .append($('<option></option>')
                              .attr('value', '56')
                              .text('Chiapas'));
              $('#state_id')
                  .append($('<option></option>')
                              .attr('value', '57')
                              .text('Chihuahua'));
              $('#state_id')
                  .append($('<option></option>')
                              .attr('value', '58')
                              .text('Coahuila'));
              $('#state_id')
                  .append($('<option></option>')
                              .attr('value', '59')
                              .text('Colima'));
              $('#state_id')
                  .append($('<option></option>')
                              .attr('value', '60')
                              .text('Distrito Federal'));
              $('#state_id')
                  .append($('<option></option>')
                              .attr('value', '61')
                              .text('Durango'));
              $('#state_id')
                  .append($('<option></option>')
                              .attr('value', '62')
                              .text('Guanajuato'));
              $('#state_id')
                  .append($('<option></option>')
                              .attr('value', '63')
                              .text('Guerrero'));
              $('#state_id')
                  .append($('<option></option>')
                              .attr('value', '64')
                              .text('Hidalgo'));
              $('#state_id')
                  .append($('<option></option>')
                              .attr('value', '65')
                              .text('Jalisco'));
              $('#state_id')
                  .append($('<option></option>')
                              .attr('value', '66')
                              .text('Mexico'));
              $('#state_id')
                  .append($('<option></option>')
                              .attr('value', '67')
                              .text('Michoacan'));
              $('#state_id')
                  .append($('<option></option>')
                              .attr('value', '68')
                              .text('Morelos'));
              $('#state_id')
                  .append($('<option></option>')
                              .attr('value', '69')
                              .text('Nayarit'));
              $('#state_id')
                  .append($('<option></option>')
                              .attr('value', '70')
                              .text('Nuevo Leon'));
              $('#state_id')
                  .append($('<option></option>')
                              .attr('value', '71')
                              .text('Oaxaca'));
              $('#state_id')
                  .append($('<option></option>')
                              .attr('value', '72')
                              .text('Puebla'));
              $('#state_id')
                  .append($('<option></option>')
                              .attr('value', '73')
                              .text('Queretaro'));
              $('#state_id')
                  .append($('<option></option>')
                              .attr('value', '74')
                              .text('Quintana Roo'));
              $('#state_id')
                  .append($('<option></option>')
                              .attr('value', '75')
                              .text('San Luis Potosi'));
              $('#state_id')
                  .append($('<option></option>')
                              .attr('value', '76')
                              .text('Sinaloa'));
              $('#state_id')
                  .append($('<option></option>')
                              .attr('value', '77')
                              .text('Sonora'));
              $('#state_id')
                  .append($('<option></option>')
                              .attr('value', '78')
                              .text('Tabasco'));
              $('#state_id')
                  .append($('<option></option>')
                              .attr('value', '79')
                              .text('Tamaulipas'));
              $('#state_id')
                  .append($('<option></option>')
                              .attr('value', '80')
                              .text('Tlaxcala'));
              $('#state_id')
                  .append($('<option></option>')
                              .attr('value', '81')
                              .text('Veracruz'));
              $('#state_id')
                  .append($('<option></option>')
                              .attr('value', '82')
                              .text('Yucatan'));
              $('#state_id')
                  .append($('<option></option>')
                              .attr('value', '83')
                              .text('Zacatecas'));
            } else if ($('#country_id').val() == 'US') {
              // Enable
              $('#state_id').removeAttr('disabled');

              $('#state_id')
                  .append($('<option></option>')
                              .attr('value', '1')
                              .text('Alabama'));
              $('#state_id')
                  .append(
                      $('<option></option>').attr('value', '2').text('Alaska'));
              $('#state_id')
                  .append($('<option></option>')
                              .attr('value', '3')
                              .text('Arizona'));
              $('#state_id')
                  .append($('<option></option>')
                              .attr('value', '4')
                              .text('Arkansas'));
              $('#state_id')
                  .append($('<option></option>')
                              .attr('value', '5')
                              .text('California'));
              $('#state_id')
                  .append($('<option></option>')
                              .attr('value', '6')
                              .text('Colorado'));
              $('#state_id')
                  .append($('<option></option>')
                              .attr('value', '7')
                              .text('Connecticut'));
              $('#state_id')
                  .append($('<option></option>')
                              .attr('value', '8')
                              .text('Delaware'));
              $('#state_id')
                  .append($('<option></option>')
                              .attr('value', '9')
                              .text('District of Columbia'));
              $('#state_id')
                  .append($('<option></option>')
                              .attr('value', '10')
                              .text('Florida'));
              $('#state_id')
                  .append($('<option></option>')
                              .attr('value', '11')
                              .text('Georgia'));
              $('#state_id')
                  .append($('<option></option>')
                              .attr('value', '12')
                              .text('Hawaii'));
              $('#state_id')
                  .append(
                      $('<option></option>').attr('value', '13').text('Idaho'));
              $('#state_id')
                  .append($('<option></option>')
                              .attr('value', '14')
                              .text('Illinois'));
              $('#state_id')
                  .append($('<option></option>')
                              .attr('value', '15')
                              .text('Indiana'));
              $('#state_id')
                  .append(
                      $('<option></option>').attr('value', '16').text('Iowa'));
              $('#state_id')
                  .append($('<option></option>')
                              .attr('value', '17')
                              .text('Kansas'));
              $('#state_id')
                  .append($('<option></option>')
                              .attr('value', '18')
                              .text('Kentucky'));
              $('#state_id')
                  .append($('<option></option>')
                              .attr('value', '19')
                              .text('Louisiana'));
              $('#state_id')
                  .append(
                      $('<option></option>').attr('value', '20').text('Maine'));
              $('#state_id')
                  .append($('<option></option>')
                              .attr('value', '21')
                              .text('Maryland'));
              $('#state_id')
                  .append($('<option></option>')
                              .attr('value', '22')
                              .text('Massachusetts'));
              $('#state_id')
                  .append($('<option></option>')
                              .attr('value', '23')
                              .text('Michigan'));
              $('#state_id')
                  .append($('<option></option>')
                              .attr('value', '24')
                              .text('Minnesota'));
              $('#state_id')
                  .append($('<option></option>')
                              .attr('value', '25')
                              .text('Mississippi'));
              $('#state_id')
                  .append($('<option></option>')
                              .attr('value', '26')
                              .text('Missouri'));
              $('#state_id')
                  .append($('<option></option>')
                              .attr('value', '27')
                              .text('Montana'));
              $('#state_id')
                  .append($('<option></option>')
                              .attr('value', '28')
                              .text('Nebraska'));
              $('#state_id')
                  .append($('<option></option>')
                              .attr('value', '29')
                              .text('Nevada'));
              $('#state_id')
                  .append($('<option></option>')
                              .attr('value', '30')
                              .text('New Hampshire'));
              $('#state_id')
                  .append($('<option></option>')
                              .attr('value', '31')
                              .text('New Jersey'));
              $('#state_id')
                  .append($('<option></option>')
                              .attr('value', '32')
                              .text('New Mexico'));
              $('#state_id')
                  .append($('<option></option>')
                              .attr('value', '33')
                              .text('New York'));
              $('#state_id')
                  .append($('<option></option>')
                              .attr('value', '34')
                              .text('North Carolina'));
              $('#state_id')
                  .append($('<option></option>')
                              .attr('value', '35')
                              .text('North Dakota'));
              $('#state_id')
                  .append(
                      $('<option></option>').attr('value', '36').text('Ohio'));
              $('#state_id')
                  .append($('<option></option>')
                              .attr('value', '37')
                              .text('Oklahoma'));
              $('#state_id')
                  .append($('<option></option>')
                              .attr('value', '38')
                              .text('Oregon'));
              $('#state_id')
                  .append($('<option></option>')
                              .attr('value', '39')
                              .text('Pennsylvania'));
              $('#state_id')
                  .append($('<option></option>')
                              .attr('value', '40')
                              .text('Rhode Island'));
              $('#state_id')
                  .append($('<option></option>')
                              .attr('value', '41')
                              .text('South Carolina'));
              $('#state_id')
                  .append($('<option></option>')
                              .attr('value', '42')
                              .text('South Dakota'));
              $('#state_id')
                  .append($('<option></option>')
                              .attr('value', '43')
                              .text('Tennessee'));
              $('#state_id')
                  .append(
                      $('<option></option>').attr('value', '44').text('Texas'));
              $('#state_id')
                  .append(
                      $('<option></option>').attr('value', '45').text('Utah'));
              $('#state_id')
                  .append($('<option></option>')
                              .attr('value', '46')
                              .text('Vermont'));
              $('#state_id')
                  .append($('<option></option>')
                              .attr('value', '47')
                              .text('Virginia'));
              $('#state_id')
                  .append($('<option></option>')
                              .attr('value', '48')
                              .text('Washington'));
              $('#state_id')
                  .append($('<option></option>')
                              .attr('value', '49')
                              .text('West Virginia'));
              $('#state_id')
                  .append($('<option></option>')
                              .attr('value', '50')
                              .text('Wisconsin'));
              $('#state_id')
                  .append($('<option></option>')
                              .attr('value', '51')
                              .text('Wyoming'));
            } else {
              // Disable
              $('#state_id').attr('disabled', 'disabled');
            }
          });

      omegaup.API.getProfile(null, function(data) {
        $('#username').html(data.userinfo.username);
        $('#name').val(data.userinfo.name);
        $('#birth_date').val(omegaup.UI.formatDate(data.userinfo.birth_date));
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
        $('#recruitment_optin')
            .prop('checked', data.userinfo.recruitment_optin == 1);

        original_locale = data.userinfo.locale;
        original_school = data.userinfo.school;
        original_school_id = data.userinfo.school_id;
      });

      var formSubmit = function() {
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
          omegaup.UI.error(omegaup.T['userEditNameTooLong']);
          return false;
        }

        omegaup.API.updateProfile(
            $('#name').val(), birth_date.getTime() / 1000,
            $('#country_id').val(), $('#state_id').val(),
            $('#scholar_degree').val(), graduation_date.getTime() / 1000,
            $('#school_id').val(), $('#school').val(), $('#locale').val(),
            $('#recruitment_optin').prop('checked') ? 1 : 0,
            function(response) {
              if (response.status == 'ok') {
                if (locale_changed) {
                  window.location.reload();
                } else {
                  omegaup.UI.success(omegaup.T['userEditSuccess']);
                }
              } else {
                omegaup.UI.error(response.error);
              }
            });

        return false; // Prevent page refresh on submit
      };

      $('form#user_profile_form').submit(formSubmit);

      $('form#change-password-form')
          .submit(function() {
            var newPassword = $('#new-password-1').val();
            var newPassword2 = $('#new-password-2').val();
            if (newPassword != newPassword2) {
              omegaup.UI.error(omegaup.T['loginPasswordNotEqual']);
              return false;
            }

            var oldPassword = $('#old-password').val();

            omegaup.API.changePassword(
                oldPassword, newPassword, function(data) {
                  if (data.status == 'ok') {
                    omegaup.UI.success(omegaup.T['passwordResetResetSuccess']);
                  } else {
                    omegaup.UI.error(data.error);
                  }
                });

            // Prevent page refresh on submit
            return false;
          });
    });
