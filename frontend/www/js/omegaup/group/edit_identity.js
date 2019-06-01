import identity_Edit from '../components/identity/Edit.vue';
import {API, OmegaUp, UI, T} from '../omegaup.js';
import Vue from 'vue';

OmegaUp.on('ready', function() {
  const path = /\/group\/([^\/]+)\/editidentity\/([^\/]+)?.*/.exec(
      window.location.pathname);
  const groupAlias = path[1];
  const username = path[2];
  const payload = JSON.parse(document.getElementById('payload').innerText);

  let groupIdentities = new Vue({
    el: '#group_edit_identity',
    render: function(createElement) {
      return createElement('omegaup-identity-edit', {
        props: {
          identity: this.identity,
          username: this.username,
          countries: this.countries,
          selectedCountry: this.selectedCountry,
          selectedState: this.selectedState,
        },
        on: {
          'edit-identity-member': function(identity, countryId, stateId) {
            API.Identity.update({
                          username: identity.username,
                          name: identity.name,
                          country_id: countryId,
                          state_id: stateId,
                          school_name: identity.school,
                          group_alias: groupAlias,
                          original_username: username,
                        })
                .then(function(data) {
                  UI.success(T.groupEditMemberUpdated);
                  window.location = `/group/${groupAlias}/edit/#members`;
                })
                .fail(function(response) { UI.apiError(response); });
          },
          cancel: function() {
            window.location = `/group/${groupAlias}/edit/#members`;
          },
        },
      });
    },
    data: {
      identity: payload.identity,
      username: username,
      countries: payload.countries,
      selectedCountry: payload.identity.country_id,
      selectedState: payload.identity.state_id,
    },
    components: {
      'omegaup-identity-edit': identity_Edit,
    },
  });
});
