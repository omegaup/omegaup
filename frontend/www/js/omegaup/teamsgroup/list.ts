import teamsgroup_List from '../components/teamsgroup/List.vue';
import { OmegaUp } from '../omegaup';
import { types } from '../api_types';
import Vue from 'vue';
import * as api from '../api';
import * as ui from '../ui';
import T from '../lang';

OmegaUp.on('ready', () => {
  const payload = types.payloadParsers.TeamsGroupListPayload();
  new Vue({
    el: '#main-container',
    components: {
      'omegaup-teams-group-list': teamsgroup_List,
    },
    data: () => ({
      teamsGroups: payload.teamsGroups,
    }),
    render: function (createElement) {
      return createElement('omegaup-teams-group-list', {
        props: { teamsGroups: this.teamsGroups },
        on: {
          'archive-group': ({
            teamsGroup,
            archived,
          }: {
            teamsGroup: types.TeamsGroup;
            archived: boolean;
          }) => {
            api.TeamsGroup.update({
              alias: teamsGroup.alias,
              name: teamsGroup.name,
              description: teamsGroup.description,
              archived: archived,
            })
              .then(() => {
                teamsGroup.archived = archived;
                ui.success(
                  archived
                    ? T.teamsGroupArchivedSuccess
                    : T.teamsGroupUnarchivedSuccess,
                );
              })
              .catch(ui.apiError);
          },
        },
      });
    },
  });
});
