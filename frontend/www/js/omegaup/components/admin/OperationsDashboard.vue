<template>
  <omegaup-admin-layout
    :access-level="accessLevel"
    :current-module="currentModuleLabel"
    :selected-module="currentModule"
  >
    <omegaup-admin-settings
      v-if="currentModule === AvailableModules.Settings"
      :ephemeral-grader-enabled="ephemeralGraderEnabled"
      @update-ephemeral-grader="onUpdateEphemeralGrader"
    ></omegaup-admin-settings>
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
import admin_AdminLayout from './AdminLayout.vue';
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
    'omegaup-admin-settings': admin_Settings,
  },
})
export default class OperationsDashboard extends Vue {
  @Prop({ default: AccessLevel.Support }) accessLevel!: AccessLevel;
  @Prop({ default: AvailableModules.Home }) selectedModule!: AvailableModules;
  @Prop({ default: true }) ephemeralGraderEnabled!: boolean;

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
}
</script>
