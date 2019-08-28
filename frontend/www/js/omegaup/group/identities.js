import group_Identities from '../components/group/Identities.vue';
import {API, OmegaUp, UI, T} from '../omegaup.js';
import Vue from 'vue';
import * as CSV from '../../../third_party/js/csv.js/csv.js';

OmegaUp.on('ready', function() {
  let groupAlias = /\/group\/([^\/]+)\/?/.exec(window.location.pathname)[1];
  let groupIdentities = new Vue({
    el: '#create-identities',
    render: function(createElement) {
      return createElement('omegaup-group-identites', {
        props: {identities: this.identities, groupAlias: this.groupAlias},
        on: {
          'bulk-identities': function(identities) {
            API.Identity.bulkCreate(
                            {identities: identities, group_alias: groupAlias})
                .then(function(data) {
                  UI.success(T.groupsIdentitiesSuccessfullyCreated);
                })
                .fail(UI.apiError);
          },
          'read-csv': function(identitiesComponent, fileUpload) {
            identitiesComponent.identities = [];
            CSV.fetch({
                 file: fileUpload.files[0],
               })
                .done(function(dataset) {
                  if (dataset.fields.length != 6) {
                    UI.error(T.groupsInvalidCsv);
                    return;
                  }
                  for (let cells of dataset.records) {
                    identitiesComponent.identities.push({
                      username: identitiesComponent.groupAlias + ':' + cells[0],
                      name: cells[1],
                      password: generatePassword(),
                      country_id: cells[2],
                      state_id: cells[3],
                      gender: cells[4],
                      school_name: cells[5],
                    });
                  }
                });
          },
        },
      });
    },
    data: {identities: [], groupAlias: groupAlias},
    components: {
      'omegaup-group-identites': group_Identities,
    },
  });

  function generatePassword() {
    const validChars = 'acdefhjkmnpqruvwxyACDEFHJKLMNPQRUVWXY346';
    const len = 8;
    // Browser supports window.crypto
    if (typeof window.crypto == 'object') {
      let arr = new Uint8Array(2 * len);
      window.crypto.getRandomValues(arr);
      return Array.from(arr.filter(value => value <=
                                            (255 - 255 % validChars.length)),
                        value => validChars[value % validChars.length])
          .join('')
          .substr(0, len);
    }

    // Browser does not support window.crypto
    let password = '';
    for (var i = 0; i < len; i++) {
      password +=
          validChars.charAt(Math.floor(Math.random() * validChars.length));
    }
    return password;
  }
});
