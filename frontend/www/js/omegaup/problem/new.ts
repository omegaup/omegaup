import { OmegaUp } from '../omegaup';
import { types } from '../api_types';
import T from '../lang';
import Vue from 'vue';
import problem_New from '../components/problem/Form.vue';
import * as ui from '../ui';
import * as api from '../api';

OmegaUp.on('ready', () => {
  const payload = types.payloadParsers.ProblemFormPayload(
    'problem-new-payload',
  );
  if (payload.statusError) {
    ui.error(payload.statusError);
  }
  const problemNew = new Vue({
    el: '#problem-new',
    render: function(createElement) {
      return createElement('omegaup-problem-new', {
        props: {
          data: payload,
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
      'omegaup-problem-new': problem_New,
    },
  });
});
