import { OmegaUp } from '../omegaup';
import { types } from '../api_types';
import T from '../lang';
import Vue from 'vue';
import problem_New from '../components/problem/Form.vue';
import * as ui from '../ui';
import * as api from '../api';

OmegaUp.on('ready', () => {
  const payload = types.payloadParsers.ProblemFormPayload();
  const problemNew = new Vue({
    el: '#main-container',
    render: function (createElement) {
      return createElement('omegaup-problem-new', {
        props: {
          data: payload,
        },
        on: {
          'alias-changed': (alias: string): void => {
            api.Problem.details({ problem_alias: alias }, { quiet: true })
              .then((data) => {
                component.errors.push('problem_alias');
                ui.error(
                  ui.formatString(T.aliasAlreadyInUse, {
                    alias: ui.escape(alias),
                  }),
                );
              })
              .catch((error) => {
                if (error.httpStatusCode == 404) {
                  ui.dismissNotifications();
                  component.errors = component.errors.filter(
                    (error) => error !== 'problem_alias',
                  );
                  return;
                }
                component.errors.push(error.parameter);
                ui.apiError(error);
              });
          },
        },
        ref: 'component',
      });
    },
    components: {
      'omegaup-problem-new': problem_New,
    },
  });
  const component = <problem_New>problemNew.$refs.component;
  if (payload.statusError) {
    ui.error(payload.statusError);
  }
  if (payload.parameter) {
    component.errors.push(payload.parameter);
  }
});
