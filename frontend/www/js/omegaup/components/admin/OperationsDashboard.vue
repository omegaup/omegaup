<template>
  <omegaup-admin-layout
    :is-admin="isAdmin"
    :current-module="currentModuleLabel"
    :selected-module="currentModule"
  >
    <omegaup-admin-settings
      v-if="currentModule === 'settings'"
      :ephemeral-grader-enabled="ephemeralGraderEnabled"
      @update-ephemeral-grader="onUpdateEphemeralGrader"
    ></omegaup-admin-settings>
    <div v-else class="card">
      <div class="text-white bg-primary card-header">
        <div class="card-title h4">
          {{ T.omegaupTitleAdminOperations }}
        </div>
      </div>
      <div class="card-body">
        <p>Esqueleto funcionando</p>
      </div>
    </div>
  </omegaup-admin-layout>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import T from '../../lang';
import admin_AdminLayout from './AdminLayout.vue';
import admin_Settings from './Settings.vue';

@Component({
  components: {
    'omegaup-admin-layout': admin_AdminLayout,
    'omegaup-admin-settings': admin_Settings,
  },
})
export default class OperationsDashboard extends Vue {
  @Prop({ default: false }) isAdmin!: boolean;
  @Prop({ default: 'home' }) selectedModule!: string;
  @Prop({ default: true }) ephemeralGraderEnabled!: boolean;

  T = T;

  get currentModule(): string {
    return this.selectedModule === 'settings' && this.isAdmin
      ? 'settings'
      : 'home';
  }

  get currentModuleLabel(): string {
    return this.currentModule === 'settings'
      ? T.omegaupTitleAdminGlobalSettings
      : '';
  }

  onUpdateEphemeralGrader(value: boolean): void {
    this.$emit('update-ephemeral-grader', value);
  }
}
</script>
