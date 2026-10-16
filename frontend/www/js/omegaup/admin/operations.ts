import admin_OperationsDashboard from '../components/admin/OperationsDashboard.vue';
import { OmegaUp } from '../omegaup';
import Vue from 'vue';
import T from '../lang';
import { types } from '../api_types';
import * as api from '../api';
import * as ui from '../ui';

OmegaUp.on('ready', () => {
  const payload = types.payloadParsers.CommonPayload();

  const getModuleFromHash = (): string =>
    window.location.hash.substring(1).split('#')[0] || 'home';

  const operationsDashboard = new Vue({
    el: '#main-container',
    components: {
      'omegaup-admin-operations-dashboard': admin_OperationsDashboard,
    },
    data: () => ({
      selectedModule: getModuleFromHash(),
      ephemeralGraderEnabled: payload.ephemeralGraderEnabled,
    }),
    render: function (createElement) {
      return createElement('omegaup-admin-operations-dashboard', {
        props: {
          isAdmin: payload.isAdmin,
          selectedModule: this.selectedModule,
          ephemeralGraderEnabled: this.ephemeralGraderEnabled,
        },
        on: {
          'update-ephemeral-grader': (value: boolean) => {
            api.Admin.updateSystemSettings({ ephemeral_grader_enabled: value })
              .then(() => {
                this.ephemeralGraderEnabled = value;
                ui.success(T.ephemeralGraderUpdated);
              })
              .catch(ui.apiError);
          },
        },
      });
    },
  });

  window.addEventListener('hashchange', () => {
    operationsDashboard.selectedModule = getModuleFromHash();
  });
});
