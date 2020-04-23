import { OmegaUp } from '../omegaup';
import { types } from '../api_types';
import T from '../lang';
import Vue from 'vue';
import problem_Edit from '../components/problem/Form.vue';
import * as ui from '../ui';
import * as api from '../api_transitional';

OmegaUp.on('ready', () => {
  const payload = types.payloadParsers.ProblemFormPayload(
    'problem-edit-payload',
  );
  if (payload.statusError) {
    ui.error(payload.statusError);
  }
  const problemEdit = new Vue({
    el: '#problem-edit',
    render: function(createElement) {
      return createElement('omegaup-problem-edit', {
        props: {
          data: payload,
          isUpdate: true,
          originalVisibility: payload.visibility,
        },
        on: {
          'alias-changed': (alias: string): void => {
            api.Problem.details({ problem_alias: alias })
              .then(data => {
                if (!data.exists) {
                  ui.dismissNotifications();
                  return;
                }
                ui.error(
                  ui.formatString(T.aliasAlreadyInUse, {
                    alias: ui.escape(alias),
                  }),
                );
              })
              .catch(ui.apiError);
          },
        },
      });
    },
    components: {
      'omegaup-problem-edit': problem_Edit,
    },
  });
});
