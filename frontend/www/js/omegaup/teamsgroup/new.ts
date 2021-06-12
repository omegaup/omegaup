import teamsgroup_Create from '../components/teamsgroup/FormCreate.vue';
import { OmegaUp } from '../omegaup';
import * as api from '../api';
import * as ui from '../ui';
import T from '../lang';
import Vue from 'vue';

OmegaUp.on('ready', () => {
  new Vue({
    el: '#main-container',
    components: {
      'omegaup-teams-group-create': teamsgroup_Create,
    },
    render: function (createElement) {
      return createElement('omegaup-teams-group-create', {
        on: {
          'validate-unused-alias': (alias: string): void => {
            if (!alias) {
              return;
            }
            api.TeamsGroup.details({ team_group_alias: alias }, { quiet: true })
              .then(() => {
                ui.error(
                  ui.formatString(T.aliasAlreadyInUse, {
                    alias: ui.escape(alias),
                  }),
                );
              })
              .catch((error) => {
                if (error.httpStatusCode == 404) {
                  ui.dismissNotifications();
                  return;
                }
                ui.apiError(error);
              });
          },
          'create-teams-group': ({
            name,
            alias,
            description,
          }: {
            name: string;
            alias: string;
            description: string;
          }) => {
            api.TeamsGroup.create({
              alias: alias,
              name: name,
              description: description,
            })
              .then(() => {
                window.location.replace(`/teamsgroup/${alias}/edit/#teams`);
              })
              .catch(ui.apiError);
          },
        },
      });
    },
  });
});
