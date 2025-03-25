import teamsgroup_List from '../components/teamsgroup/List.vue';
import { OmegaUp } from '../omegaup';
import { types } from '../api_types';
import Vue from 'vue';

OmegaUp.on('ready', () => {
  const payload = types.payloadParsers.TeamsGroupListPayload();
  new Vue({
    el: '#main-container',
    components: {
      'omegaup-teams-group-list': teamsgroup_List,
    },
    render: function (createElement) {
      return createElement('omegaup-teams-group-list', {
        props: { teamsGroups: payload.teamsGroups },
      });
    },
  });
});
