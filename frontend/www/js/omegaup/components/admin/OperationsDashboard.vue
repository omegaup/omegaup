<template>
  <omegaup-admin-layout
    :access-level="accessLevel"
    :current-module="currentModuleLabel"
    :selected-module="currentModule"
  >
    <template v-if="currentModule === AvailableModules.Settings">
      <omegaup-admin-settings
        :ephemeral-grader-enabled="ephemeralGraderEnabled"
        @update-ephemeral-grader="onUpdateEphemeralGrader"
      ></omegaup-admin-settings>
      <omegaup-admin-maintenance-mode
        class="mt-4"
        :maintenance-enabled="maintenanceMode.enabled"
        :maintenance-message-es="maintenanceMode.message_es || ''"
        :maintenance-message-en="maintenanceMode.message_en || ''"
        :maintenance-message-pt="maintenanceMode.message_pt || ''"
        :maintenance-type="maintenanceMode.type"
        :maintenance-predefined-templates="maintenancePredefinedTemplates"
        :preferred-language="preferredLanguage"
        @toggle-maintenance="onToggleMaintenance"
        @save-maintenance="onSaveMaintenance"
      ></omegaup-admin-maintenance-mode>
    </template>
    <div v-else class="card">
      <div class="text-white bg-primary card-header">
        <div class="card-title h4">
          {{ T.omegaupTitleAdminOperations }}
        </div>
      </div>
    </div>
  </omegaup-admin-layout>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import T from '../../lang';
import { types } from '../../api_types';
import admin_AdminLayout from './AdminLayout.vue';
import admin_MaintenanceMode, {
  MaintenanceSettings,
} from './MaintenanceMode.vue';
import admin_Settings from './Settings.vue';
import {
  AccessLevel,
  AvailableModules,
  MODULE_ACCESS_LEVELS,
  hasAccessLevel,
} from '../../admin/operations_modules';

@Component({
  components: {
    'omegaup-admin-layout': admin_AdminLayout,
    'omegaup-admin-maintenance-mode': admin_MaintenanceMode,
    'omegaup-admin-settings': admin_Settings,
  },
})
export default class OperationsDashboard extends Vue {
  @Prop({ default: AccessLevel.Support }) accessLevel!: AccessLevel;
  @Prop({ default: AvailableModules.Home }) selectedModule!: AvailableModules;
  @Prop({ default: true }) ephemeralGraderEnabled!: boolean;
  @Prop({
    default: () => ({
      enabled: false,
      message_es: '',
      message_en: '',
      message_pt: '',
      type: 'info',
    }),
  })
  maintenanceMode!: types.MaintenanceModeStatus;
  @Prop({ default: () => [] })
  maintenancePredefinedTemplates!: types.PredefinedTemplate[];
  @Prop({ default: 'es' }) preferredLanguage!: string;

  T = T;
  AvailableModules = AvailableModules;

  get currentModule(): AvailableModules {
    return hasAccessLevel(
      this.accessLevel,
      MODULE_ACCESS_LEVELS[this.selectedModule],
    )
      ? this.selectedModule
      : AvailableModules.Home;
  }

  get currentModuleLabel(): string {
    return this.currentModule === AvailableModules.Settings
      ? T.omegaupTitleAdminGlobalSettings
      : '';
  }

  onUpdateEphemeralGrader(value: boolean): void {
    this.$emit('update-ephemeral-grader', value);
  }

  onToggleMaintenance(enabled: boolean): void {
    this.$emit('toggle-maintenance', enabled);
  }

  onSaveMaintenance(settings: MaintenanceSettings): void {
    this.$emit('save-maintenance', settings);
  }
}
</script>
