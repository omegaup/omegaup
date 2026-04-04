import group_New from '../components/group/Form.vue';
import { OmegaUp } from '../omegaup';
import * as api from '../api';
import * as ui from '../ui';
import T from '../lang';
import Vue from 'vue';

OmegaUp.on('ready', function () {
  new Vue({
    el: '#main-container',
    components: {
      'omegaup-group-new': group_New,
    },
    render: function (createElement) {
      return createElement('omegaup-group-new', {
        props: {
          T: T,
        },
        on: {
          'validate-unused-alias': (alias: string): void => {
            if (!alias) {
              return;
            }
            api.Group.details({ group_alias: alias }, { quiet: true })
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
          'create-group': (
            name: string,
            alias: string,
            description: string,
          ) => {
            api.Group.create({
              alias: alias,
              name: name,
              description: description,
            })
              .then(() => {
                window.location.replace(`/group/${alias}/edit/#members`);
              })
              .catch(ui.apiError);
          },
        },
      });
    },
  });
});
