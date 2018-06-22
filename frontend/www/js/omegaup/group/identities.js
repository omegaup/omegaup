import group_Identities from '../components/group/Identities.vue';
import {API, OmegaUp, UI, T} from '../omegaup.js';
import Vue from 'vue';

OmegaUp.on('ready', function() {
  let groupAlias = /\/group\/([^\/]+)\/?/.exec(window.location.pathname)[1];
  let groupIdentities = new Vue({
    el: '#create_identities',
    render: function(createElement) {
      return createElement('omegaup-group-identites', {
        props: {identities: this.identities, groupAlias: this.groupAlias},
        on: {
          'bulk-identities': (identities) =>
                                 this.createIdentities(identities, groupAlias),
        },
      });
    },
    data: {identities: [], groupAlias: groupAlias},
    components: {
      'omegaup-group-identites': group_Identities,
    },
    methods: {
      createIdentities: function(identities, groupAlias) {
        API.Identity.bulkCreate(
                        {identities: identities, group_alias: groupAlias})
            .then(function(data) {
              UI.success(T.groupsIdentitiesSuccessfullyCreated);
            })
            .fail(UI.apiError);
      }
    }
  });
});
