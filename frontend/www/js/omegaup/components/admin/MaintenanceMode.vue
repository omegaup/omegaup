<template>
  <div class="card">
    <div class="card-header bg-warning text-dark">
      <h5 class="mb-0">
        <font-awesome-icon icon="wrench" />
        {{ T.maintenanceModeTitle }}
      </h5>
    </div>
    <div class="card-body">
      <div class="form-group">
        <omegaup-toggle-switch
          :checked-value="currentMaintenanceEnabled"
          @update:value="onToggleMaintenance"
        >
          <template #switch-text>
            <span class="switch-text">
              <strong>{{
                currentMaintenanceEnabled
                  ? T.maintenanceModeActive
                  : T.maintenanceModeInactive
              }}</strong>
            </span>
          </template>
        </omegaup-toggle-switch>
      </div>
      <div v-if="currentMaintenanceEnabled" class="form-group">
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

        <div class="mb-2">
          <span
            v-for="type in maintenanceTypes"
            :key="type.value"
            :class="[
              'badge',
              'mr-3',
              'p-2',
              badgeClass(type.value),
              {
                'badge-selected': currentMaintenanceType === type.value,
              },
            ]"
            @click="currentMaintenanceType = type.value"
          >
            {{ type.label }}
          </span>
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
      </div>
      <button
        v-if="currentMaintenanceEnabled"
        class="btn btn-primary"
        type="button"
        @click="onSaveMaintenance"
      >
        {{ T.wordsSaveChanges }}
      </button>
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
.badge {
  cursor: pointer;
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
