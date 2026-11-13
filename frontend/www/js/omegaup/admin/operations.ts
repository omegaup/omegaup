import admin_OperationsDashboard from '../components/admin/OperationsDashboard.vue';
import { MaintenanceSettings } from '../components/admin/MaintenanceMode.vue';
import { OmegaUp } from '../omegaup';
import Vue from 'vue';
import T from '../lang';
import { types } from '../api_types';
import * as api from '../api';
import * as ui from '../ui';
import { AccessLevel, getModuleFromHash } from './operations_modules';

function saveMaintenance(
  settings: MaintenanceSettings,
  message: string,
  onSaved: () => void,
  onError?: () => void,
): void {
  api.Admin.setMaintenanceMode(settings)
    .then(() => {
      onSaved();
      ui.success(message);
    })
    .catch((error) => {
      ui.apiError(error);
      onError?.();
    });
}

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
      maintenanceVersion: 0,
    }),
    render: function (createElement) {
      return createElement('omegaup-admin-operations-dashboard', {
        props: {
          accessLevel: payload.accessLevel as AccessLevel,
          selectedModule: this.module,
          ephemeralGraderEnabled: this.ephemeralGraderEnabled,
          maintenanceMode: this.maintenanceMode,
          maintenanceVersion: this.maintenanceVersion,
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
            // Enabling is saved with the "save" button, once the message is
            // written. Disabling is saved right away.
            if (enabled) {
              return;
            }
            const settings = {
              enabled: false,
              message_es: '',
              message_en: '',
              message_pt: '',
              type: 'info',
            };
            saveMaintenance(
              settings,
              T.maintenanceModeInactive,
              () => {
                // Keep the saved state, so it is not lost when the user
                // switches between sections of the dashboard.
                this.maintenanceMode = { ...settings };
              },
              () => {
                // The request failed: the server still has maintenance
                // enabled, so the component is recreated from the saved
                // state instead of showing it as disabled.
                this.maintenanceVersion += 1;
              },
            );
          },
          'save-maintenance': (settings: MaintenanceSettings) => {
            saveMaintenance(settings, T.maintenanceModeActive, () => {
              this.maintenanceMode = { ...settings };
            });
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
