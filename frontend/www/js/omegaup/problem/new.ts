import { OmegaUp } from '../omegaup';
import { types } from '../api_types';
import T from '../lang';
import Vue from 'vue';
import problem_New from '../components/problem/Form.vue';
import * as ui from '../ui';
import * as api from '../api_transitional';

OmegaUp.on('ready', () => {
  const payload = types.payloadParsers.ProblemFormPayload('payload');
  const problemNew = new Vue({
    el: '#problem-new',
    render: function(createElement) {
      return createElement('omegaup-problem-new', {
        props: {
          data: this.data,
        },
        on: {
          'alias-in-use': (alias: string): void => {
            api.Problem.details({ problem_alias: alias })
              .then(data => {
                if (!data.exists) {
                  ui.dismissNotifications(10);
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
    data: {
      data: payload,
    },
    components: {
      'omegaup-problem-new': problem_New,
    },
  });
});
