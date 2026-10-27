import admin_OperationsDashboard from '../components/admin/OperationsDashboard.vue';
import { MaintenanceSettings } from '../components/admin/MaintenanceMode.vue';
import { OmegaUp } from '../omegaup';
import Vue from 'vue';
import T from '../lang';
import { types } from '../api_types';
import * as api from '../api';
import * as ui from '../ui';
import { AccessLevel, getModuleFromHash } from './operations_modules';

OmegaUp.on('ready', () => {
  const payload = types.payloadParsers.OperationsDashboardPayload();
  const commonPayload = types.payloadParsers.CommonPayload();

  const operationsDashboard = new Vue({
    el: '#main-container',
    components: {
      'omegaup-admin-operations-dashboard': admin_OperationsDashboard,
    },
    data: () => ({
      module: getModuleFromHash(window.location.hash),
      ephemeralGraderEnabled: commonPayload.ephemeralGraderEnabled,
      maintenanceMode: payload.maintenanceMode,
    }),
    methods: {
      saveMaintenance(settings: MaintenanceSettings, message: string): void {
        api.Admin.setMaintenanceMode(settings)
          .then(() => {
            this.maintenanceMode = { ...settings };
            ui.success(message);
          })
          .catch(ui.apiError);
      },
    },
    render: function (createElement) {
      return createElement('omegaup-admin-operations-dashboard', {
        props: {
          accessLevel: payload.accessLevel as AccessLevel,
          selectedModule: this.module,
          ephemeralGraderEnabled: this.ephemeralGraderEnabled,
          maintenanceMode: this.maintenanceMode,
          maintenancePredefinedTemplates:
            payload.maintenancePredefinedTemplates,
          preferredLanguage: commonPayload.preferredLanguage,
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
          'toggle-maintenance': (enabled: boolean) => {
            if (enabled) {
              return;
            }
            this.saveMaintenance(
              {
                enabled: false,
                message_es: '',
                message_en: '',
                message_pt: '',
                type: 'info',
              },
              T.maintenanceModeInactive,
            );
          },
          'save-maintenance': (settings: MaintenanceSettings) => {
            this.saveMaintenance(settings, T.maintenanceModeActive);
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
