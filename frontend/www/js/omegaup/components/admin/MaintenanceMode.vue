<template>
  <div class="card">
    <div class="card-body">
      <div class="setting-card">
        <font-awesome-icon icon="bullhorn" class="setting-icon" />
        <h5 class="setting-title">{{ T.maintenanceModeTitle }}</h5>
        <p class="setting-description text-muted mb-0">
          {{ T.maintenanceModeDescription }}
        </p>
        <div class="setting-control">
          <strong class="mr-3">{{
            currentMaintenanceEnabled
              ? T.maintenanceModeActive
              : T.maintenanceModeInactive
          }}</strong>
          <omegaup-toggle-switch
            :checked-value="currentMaintenanceEnabled"
            @update:value="onToggleMaintenance"
          >
            <template #switch-text>
              <span class="sr-only">{{ T.maintenanceModeTitle }}</span>
            </template>
          </omegaup-toggle-switch>
        </div>
      </div>

      <div v-if="currentMaintenanceEnabled" class="mt-4">
        <div class="notice d-flex align-items-center mb-3" role="note">
          <font-awesome-icon icon="info-circle" class="mr-2" />
          {{ T.maintenanceModeNotice }}
        </div>

        <select
          v-model="selectedTemplateId"
          class="form-control mb-3"
          @change="onSelectTemplate"
        >
          <option value="">
            -- {{ T.maintenanceModeTemplateSelectPlaceholder }} --
          </option>
          <option
            v-for="template in maintenancePredefinedTemplates"
            :key="template.id"
            :value="template.id"
          >
            {{ template.title[preferredLanguage] }}
          </option>
          <option value="custom">
            {{ T.maintenanceModeTemplateCustomOption }}
          </option>
        </select>

        <div class="mb-3">
          <label class="d-block">{{ T.maintenanceModeTypeLabel }}</label>
          <button
            v-for="type in maintenanceTypes"
            :key="type.value"
            type="button"
            :class="[
              'btn',
              'mr-2',
              'mb-2',
              typeButtonClass(type.value),
              { active: currentMaintenanceType === type.value },
            ]"
            :aria-pressed="
              currentMaintenanceType === type.value ? 'true' : 'false'
            "
            @click="currentMaintenanceType = type.value"
          >
            {{ type.label }}
          </button>
        </div>

        <label>{{ T.maintenanceModeMessage }}</label>
        <div class="row mb-2">
          <div class="col-auto d-flex align-items-center">
            <span class="badge badge-secondary w-100">{{
              T.wordsSpanish
            }}</span>
          </div>
          <div class="col">
            <textarea
              v-model="currentMaintenanceMessageEs"
              :class="['form-control', badgeClass(currentMaintenanceType)]"
              :placeholder="T.maintenanceModeMessagePlaceholder"
              @input="autoResize($event)"
            ></textarea>
          </div>
        </div>
        <div class="row mb-2">
          <div class="col-auto d-flex align-items-center">
            <span class="badge badge-secondary w-100">{{
              T.wordsEnglish
            }}</span>
          </div>
          <div class="col">
            <textarea
              v-model="currentMaintenanceMessageEn"
              :class="['form-control', badgeClass(currentMaintenanceType)]"
              :placeholder="T.maintenanceModeMessagePlaceholder"
              @input="autoResize($event)"
            ></textarea>
          </div>
        </div>
        <div class="row mb-2">
          <div class="col-auto d-flex align-items-center">
            <span class="badge badge-secondary w-100">{{
              T.wordsPortuguese
            }}</span>
          </div>
          <div class="col">
            <textarea
              v-model="currentMaintenanceMessagePt"
              :class="['form-control', badgeClass(currentMaintenanceType)]"
              :placeholder="T.maintenanceModeMessagePlaceholder"
              @input="autoResize($event)"
            ></textarea>
          </div>
        </div>

        <div v-if="previewLines.length > 0" class="mt-3">
          <label class="mb-1">{{ T.wordsPreview }}</label>
          <div
            :class="['alert', 'mb-1', badgeClass(currentMaintenanceType)]"
            data-maintenance-preview
          >
            <div v-for="(line, index) in previewLines" :key="index">
              {{ line }}
            </div>
          </div>
          <small class="form-text text-muted">
            {{ T.maintenanceModePreviewHelp }}
          </small>
        </div>

        <small class="form-text text-muted mt-3">
          {{ T.maintenanceModeSaveHelp }}
        </small>

        <div class="mt-3 d-flex flex-column-reverse flex-md-row">
          <button
            class="btn btn-secondary mt-2 mt-md-0 mr-md-2"
            type="button"
            data-maintenance-cancel
            @click="onCancel"
          >
            {{ T.wordsCancel }}
          </button>
          <button
            class="btn btn-primary"
            type="button"
            data-maintenance-save
            @click="onSaveMaintenance"
          >
            {{ T.wordsSaveChanges }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop, Emit, Watch } from 'vue-property-decorator';
import T from '../../lang';
import { types } from '../../api_types';
import omegaup_ToggleSwitch from '../ToggleSwitch.vue';

import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { fas } from '@fortawesome/free-solid-svg-icons';
import { library } from '@fortawesome/fontawesome-svg-core';
library.add(fas);

export enum MaintenanceType {
  Info = 'info',
  Warning = 'warning',
  Error = 'danger',
}

export interface MaintenanceSettings {
  enabled: boolean;
  message_es: string;
  message_en: string;
  message_pt: string;
  type: string;
}

@Component({
  components: {
    'font-awesome-icon': FontAwesomeIcon,
    'omegaup-toggle-switch': omegaup_ToggleSwitch,
  },
})
export default class AdminMaintenanceMode extends Vue {
  @Prop() maintenanceEnabled!: boolean;
  @Prop() maintenanceMessageEs!: string;
  @Prop() maintenanceMessageEn!: string;
  @Prop() maintenanceMessagePt!: string;
  @Prop() maintenanceType!: string;
  @Prop() maintenancePredefinedTemplates!: types.PredefinedTemplate[];
  @Prop() preferredLanguage!: string;

  selectedTemplateId: string = '';

  currentMaintenanceEnabled = this.maintenanceEnabled;
  currentMaintenanceMessageEs = this.maintenanceMessageEs;
  currentMaintenanceMessageEn = this.maintenanceMessageEn;
  currentMaintenanceMessagePt = this.maintenanceMessagePt;
  currentMaintenanceType = this.maintenanceType || 'info';

  T = T;
  MaintenanceType = MaintenanceType;

  maintenanceTypes = [
    { value: MaintenanceType.Info, label: this.T.maintenanceModeTypeInfo },
    {
      value: MaintenanceType.Warning,
      label: this.T.maintenanceModeTypeWarning,
    },
    { value: MaintenanceType.Error, label: this.T.maintenanceModeTypeError },
  ];

  get previewLines(): string[] {
    const messages: { [language: string]: string } = {
      es: this.currentMaintenanceMessageEs,
      en: this.currentMaintenanceMessageEn,
      pt: this.currentMaintenanceMessagePt,
    };
    const message =
      messages[this.preferredLanguage] ||
      this.currentMaintenanceMessageEs ||
      this.currentMaintenanceMessageEn ||
      this.currentMaintenanceMessagePt ||
      '';
    return message
      .split(/<br\s*\/?>/i)
      .map((line) => line.trim())
      .filter((line) => line !== '');
  }

  badgeClass(type: string) {
    switch (type) {
      case 'warning':
        return 'alert-warning';
      case 'danger':
        return 'alert-danger';
      default:
        return 'alert-info';
    }
  }

  typeButtonClass(type: string): string {
    switch (type) {
      case 'warning':
        return 'btn-outline-warning';
      case 'danger':
        return 'btn-outline-danger';
      default:
        return 'btn-outline-info';
    }
  }

  autoResize(event: Event) {
    const textarea = event.target as HTMLTextAreaElement;
    textarea.style.height = 'auto';
    textarea.style.height = textarea.scrollHeight + 'px';
  }

  @Watch('maintenanceEnabled')
  onMaintenanceEnabledChange(newValue: boolean) {
    this.currentMaintenanceEnabled = newValue;
  }

  @Watch('maintenanceMessageEs')
  onMaintenanceMessageEsChange(newValue: string) {
    this.currentMaintenanceMessageEs = newValue;
  }

  @Watch('maintenanceMessageEn')
  onMaintenanceMessageEnChange(newValue: string) {
    this.currentMaintenanceMessageEn = newValue;
  }

  @Watch('maintenanceMessagePt')
  onMaintenanceMessagePtChange(newValue: string) {
    this.currentMaintenanceMessagePt = newValue;
  }

  @Watch('maintenanceType')
  onMaintenanceTypeChange(newValue: string) {
    this.currentMaintenanceType = newValue;
  }

  @Emit('toggle-maintenance')
  onToggleMaintenance(newValue: boolean): boolean {
    this.currentMaintenanceEnabled = newValue;
    if (!newValue) {
      this.currentMaintenanceMessageEs = '';
      this.currentMaintenanceMessageEn = '';
      this.currentMaintenanceMessagePt = '';
      this.currentMaintenanceType = 'info';
    }
    return newValue;
  }

  @Emit('save-maintenance')
  onSaveMaintenance(): MaintenanceSettings {
    return {
      enabled: this.currentMaintenanceEnabled,
      message_es: this.currentMaintenanceMessageEs,
      message_en: this.currentMaintenanceMessageEn,
      message_pt: this.currentMaintenanceMessagePt,
      type: this.currentMaintenanceType,
    };
  }

  onCancel(): void {
    this.currentMaintenanceEnabled = this.maintenanceEnabled;
    this.currentMaintenanceMessageEs = this.maintenanceMessageEs;
    this.currentMaintenanceMessageEn = this.maintenanceMessageEn;
    this.currentMaintenanceMessagePt = this.maintenanceMessagePt;
    this.currentMaintenanceType = this.maintenanceType || 'info';
    this.selectedTemplateId = '';
  }

  onSelectTemplate() {
    const template = this.maintenancePredefinedTemplates.find(
      (t) => t.id === this.selectedTemplateId,
    );
    if (template && template.id !== 'custom') {
      this.currentMaintenanceMessageEs = `${template.title.es}<br>${template.message.es}`;
      this.currentMaintenanceMessageEn = `${template.title.en}<br>${template.message.en}`;
      this.currentMaintenanceMessagePt = `${template.title.pt}<br>${template.message.pt}`;
      this.currentMaintenanceType = template.type;
      return;
    }
    this.currentMaintenanceMessageEs = '';
    this.currentMaintenanceMessageEn = '';
    this.currentMaintenanceMessagePt = '';
    this.currentMaintenanceType = 'info';
  }
}
</script>

<style lang="scss" scoped>
.setting-card {
  display: grid;
  grid-template-columns: auto 1fr auto;
  grid-template-areas:
    'icon title control'
    'icon description control';
  column-gap: 1rem;
  align-items: center;
}

.setting-icon {
  grid-area: icon;
  font-size: 1.5rem;
  color: var(--admin-dashboard-setting-icon-color);
}

.setting-title {
  grid-area: title;
  align-self: end;
  margin-bottom: 0.25rem;
}

.setting-description {
  grid-area: description;
  align-self: start;
}

.setting-control {
  grid-area: control;
  display: flex;
  align-items: center;
  justify-content: flex-end;

  .switch-container {
    width: auto;
    margin-bottom: 0;
    flex-shrink: 0;
  }
}

@media (max-width: 767.98px) {
  .setting-card {
    grid-template-columns: auto 1fr;
    grid-template-areas:
      'icon title'
      'description description'
      'control control';
    row-gap: 0.75rem;
  }

  .setting-title {
    align-self: center;
    margin-bottom: 0;
  }

  .setting-control {
    justify-content: space-between;
    border-top: 1px solid var(--admin-dashboard-sidebar-border-color);
    padding-top: 0.75rem;
  }
}

.notice {
  background: var(--admin-dashboard-notice-background-color);
  border: 1px solid var(--admin-dashboard-notice-border-color);
  border-radius: 0.375rem;
  color: var(--admin-dashboard-notice-font-color);
  font-size: 0.875rem;
  padding: 0.75rem 1rem;
}

.row.mb-2 {
  .col-auto {
    display: flex;
    align-items: stretch;
    .badge {
      display: flex;
      align-items: center;
      justify-content: center;
      height: 100%;
      min-width: 100px;
      font-size: 1rem;
      border-radius: 0.25rem;
    }
  }
  .col {
    display: flex;
    align-items: stretch;
    textarea {
      height: 100%;
      min-height: 40px;
      font-size: 1rem;
      padding-top: 8px;
      padding-bottom: 8px;
      border-radius: 0.25rem;
    }
  }
}
</style>
