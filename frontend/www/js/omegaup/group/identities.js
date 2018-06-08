import group_Identities from '../components/group/Identities.vue';
import {API, OmegaUp, UI, T} from '../omegaup.js';
import Vue from 'vue';

OmegaUp.on('ready', function() {
  function fillMembersGroupTable() {
    UI.success(T.groupsIdentitiesSuccessfullyCreated);
  }

  let groupAlias = /\/group\/([^\/]+)\/?/.exec(window.location.pathname)[1];
  let groupIdentities = new Vue({
    el: '#create_identities',
    render: function(createElement) {
      return createElement('omegaup-group-identites', {
        props: {identities: this.identities},
        on: {
          'bulk-identities': (identities) =>
                                 this.createIdentities(identities, groupAlias),
        },
      });
    },
    data: {
      identities: [],
    },
    components: {
      'omegaup-group-identites': group_Identities,
    },
    methods: {
      createIdentities: function(identities, groupAlias) {
        UI.bulkOperation(function(identity, resolve, reject) {
          if (identity['username'] != null) {
            API.Identity.create({
                          group_alias: groupAlias,
                          username: identity['username'],
                          user: identity['user'],
                          password: identity['password'],
                          country_id: identity['country_id'],
                          state_id: identity['state_id'],
                          gender: identity['gender'],
                          school_name: identity['school_name']
                        })
                .then(resolve)
                .fail(reject);
          }
        }, fillMembersGroupTable, null, identities);
      }
    }
  });
});
