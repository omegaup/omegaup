import teamsgroup_Edit, {
  AvailableTabs,
} from '../components/teamsgroup/Edit.vue';
import { OmegaUp } from '../omegaup';
import { types } from '../api_types';
import * as api from '../api';
import * as ui from '../ui';
import T from '../lang';
import Vue from 'vue';

OmegaUp.on('ready', () => {
  const payload = types.payloadParsers.TeamGroupEditPayload();
  new Vue({
    el: '#main-container',
    components: {
      'omegaup-teams-group-edit': teamsgroup_Edit,
    },
    render: function (createElement) {
      return createElement('omegaup-teams-group-edit', {
        props: {
          alias: payload.teamGroup.alias,
          name: payload.teamGroup.name,
          description: payload.teamGroup.description,
          countries: payload.countries,
          isOrganizer: payload.isOrganizer,
          tab: window.location.hash
            ? window.location.hash.substr(1)
            : AvailableTabs.Teams,
          teamsIdentities: payload.identities,
          userErrorRow: null,
          searchResultUsers: [] as types.ListItem[],
        },
        on: {
          'update-teams-group': (request: {
            name: string;
            description: string;
          }) => {
            api.TeamsGroup.update({
              alias: payload.teamGroup.alias,
              name: request.name,
              description: request.description,
            })
              .then(() => {
                ui.success(T.teamsGroupEditGroupUpdated);
              })
              .catch(ui.apiError);
          },
        },
      });
    },
  });
});
