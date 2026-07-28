import { OmegaUp } from '../omegaup';
import { types } from '../api_types';
import T from '../lang';
import Vue from 'vue';
import problem_New from '../components/problem/Form.vue';
import { CreationMethods } from '../components/problem/Form.vue';
import * as ui from '../ui';
import * as api from '../api';
import {
  downloadInputFile,
  downloadZipFile,
  showUpdateSuccessMessage,
} from './creator/downloadHandlers';
import { BootstrapVue, BootstrapVueIcons } from 'bootstrap-vue';
import 'bootstrap/dist/css/bootstrap.css';
import 'bootstrap-vue/dist/bootstrap-vue.css';

Vue.use(BootstrapVue);
Vue.use(BootstrapVueIcons);

OmegaUp.on('ready', () => {
  const payload = types.payloadParsers.ProblemFormPayload();
  const showCreationMethodSelector =
    OmegaUp.experiments?.isEnabled('problem_creation_method_selector') ?? false;

  const creationMethod = showCreationMethodSelector
    ? CreationMethods.Creator
    : CreationMethods.Zip;

  if (payload.statusError) {
    ui.error(payload.statusError);
  }
  const problemNew = new Vue({
    el: '#main-container',
    components: {
      'omegaup-problem-new': problem_New,
    },
    data: () => ({
      errors: payload.parameter ? [payload.parameter] : [],
    }),
    render: function (createElement) {
      return createElement('omegaup-problem-new', {
        props: {
          data: payload,
          errors: this.errors,
          hasVisitedSection: payload.hasVisitedSection,
          showCreationMethodSelector,
          creationMethod,
        },
        on: {
          'alias-changed': (alias: string): void => {
            if (!alias) {
              problemNew.errors.push('problem_alias');
              return;
            }
            api.Problem.details({ problem_alias: alias }, { quiet: true })
              .then(() => {
                problemNew.errors.push('problem_alias');
                ui.error(
                  ui.formatString(T.aliasAlreadyInUse, {
                    alias: ui.escape(alias),
                  }),
                );
              })
              .catch((error) => {
                if (error.httpStatusCode == 404) {
                  ui.dismissNotifications();
                  problemNew.errors = problemNew.errors.filter(
                    (error) => error !== 'problem_alias',
                  );
                  return;
                }
                problemNew.errors.push(error.parameter);
                ui.apiError(error);
              });
          },
          'show-update-success-message': showUpdateSuccessMessage,
          'download-input-file': downloadInputFile,
          'download-zip-file': downloadZipFile,
        },
      });
    },
  });
});
