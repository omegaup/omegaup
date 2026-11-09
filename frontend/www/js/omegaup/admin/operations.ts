import admin_OperationsDashboard from '../components/admin/OperationsDashboard.vue';
import { OmegaUp } from '../omegaup';
import Vue from 'vue';
import T from '../lang';
import { types } from '../api_types';
import * as api from '../api';
import * as ui from '../ui';
import { getAccessLevel, getModuleFromHash } from './operations_modules';

OmegaUp.on('ready', () => {
  const payload = types.payloadParsers.CommonPayload();

  const operationsDashboard = new Vue({
    el: '#main-container',
    components: {
      'omegaup-admin-operations-dashboard': admin_OperationsDashboard,
    },
    data: () => ({
      module: getModuleFromHash(window.location.hash),
      ephemeralGraderEnabled: payload.ephemeralGraderEnabled,
    }),
    render: function (createElement) {
      return createElement('omegaup-admin-operations-dashboard', {
        props: {
          accessLevel: getAccessLevel(payload.isAdmin),
          selectedModule: this.module,
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

  const onHashChange = () => {
    operationsDashboard.module = getModuleFromHash(window.location.hash);
  };
  window.addEventListener('hashchange', onHashChange);
  operationsDashboard.$once('hook:beforeDestroy', () => {
    window.removeEventListener('hashchange', onHashChange);
  });
});
