<template>
  <div class="card">
    <div class="text-white bg-primary card-header">
      <div class="card-title h4">
        {{ T.omegaupTitleSupportDashboard }}
        <span v-if="username != null">- {{ username }} ({{ email }})</span>
        <span v-else-if="contestAlias != null"
          >- {{ contestTitle }} ({{ contestAlias }})</span
        >
      </div>
    </div>
    <div class="card-body">
      <!-- Maintenance Mode Section -->
      <div class="row mb-4">
        <div class="col-md-12">
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
                <label
                  >{{ T.maintenanceModeMessage }} ({{ T.wordsSpanish }})</label
                >
                <textarea
                  v-model="currentMaintenanceMessageEs"
                  class="form-control"
                  rows="3"
                  :placeholder="T.maintenanceModeMessagePlaceholder"
                ></textarea>

                <label class="mt-3"
                  >{{ T.maintenanceModeMessage }} ({{ T.wordsEnglish }})</label
                >
                <textarea
                  v-model="currentMaintenanceMessageEn"
                  class="form-control"
                  rows="3"
                  :placeholder="T.maintenanceModeMessagePlaceholder"
                ></textarea>

                <label class="mt-3"
                  >{{ T.maintenanceModeMessage }} ({{
                    T.wordsPortuguese
                  }})</label
                >
                <textarea
                  v-model="currentMaintenanceMessagePt"
                  class="form-control"
                  rows="3"
                  :placeholder="T.maintenanceModeMessagePlaceholder"
                ></textarea>

                <label class="mt-3">{{ T.maintenanceModeType }}</label>
                <select v-model="currentMaintenanceType" class="form-control">
                  <option :value="MaintenanceType.Info">
                    {{ T.maintenanceModeTypeInfo }}
                  </option>
                  <option :value="MaintenanceType.Warning">
                    {{ T.maintenanceModeTypeWarning }}
                  </option>
                  <option :value="MaintenanceType.Error">
                    {{ T.maintenanceModeTypeError }}
                  </option>
                </select>
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
        </div>
      </div>

      <div class="row mb-3">
        <div class="col-md-6">
          <form class="form w-100" @submit.prevent="onSearchEmail">
            <div class="input-group">
              <input
                v-model="usernameOrEmail"
                class="form-control"
                name="email"
                type="text"
                required="required"
                :disabled="username != null"
                :placeholder="T.supportTypeEmailOrUsername"
              />
              <div class="input-group-append">
                <button
                  class="btn btn-outline-secondary"
                  type="submit"
                  :disabled="username != null"
                >
                  {{ T.wordsSearch }}
                </button>
              </div>
            </div>
          </form>
        </div>
        <div class="col-md-6 text-right">
          <button
            v-if="username != null"
            class="btn btn-secondary"
            type="reset"
            @click.prevent="onReset"
          >
            {{ T.supportNewSearch }}
          </button>
        </div>
      </div>
      <div class="row mb-3">
        <div class="col-md-6">
          <form class="form w-100" @submit.prevent="onSearchContest">
            <div class="input-group">
              <input
                v-model="currentContestAlias"
                class="form-control"
                name="contest_alias"
                type="text"
                required="required"
                :disabled="contestFound"
                :placeholder="T.supportTypeContestAlias"
              />
              <div class="input-group-append">
                <button
                  class="btn btn-outline-secondary"
                  type="submit"
                  :disabled="contestFound"
                >
                  {{ T.wordsSearch }}
                </button>
              </div>
            </div>
          </form>
        </div>
        <div class="col-md-6 text-right">
          <button
            v-if="contestFound"
            class="btn btn-secondary"
            type="reset"
            @click.prevent="onResetContest"
          >
            {{ T.supportNewSearch }}
          </button>
        </div>
      </div>
      <template v-if="contestFound">
        <div class="row mb-3">
          <div class="col-md-12">
            <h4>{{ T.supportOptions }}</h4>
            <div class="form-check">
              <label class="form-check-label">
                <input
                  v-model="currentIsContestRecommended"
                  class="form-check-input"
                  type="checkbox"
                  @change="onToggleRecommended"
                />
                {{ T.supportSetAsRecommended }}
              </label>
            </div>
          </div>
        </div>
      </template>
      <template v-if="username != null">
        <div class="row mb-3">
          <div class="col-md">
            <form class="form w-100" @submit.prevent="onVerifyUser">
              <button
                class="btn btn-outline-secondary"
                type="button"
                :disabled="verified"
                @click.prevent="onVerifyUser"
              >
                <template v-if="verified">
                  <font-awesome-icon icon="check" :style="{ color: 'green' }" />
                  {{ T.userVerified }}
                </template>
                <template v-else>
                  {{ T.userVerify }}
                </template>
              </button>
            </form>
          </div>
          <div data-last-login class="col-md">
            <label v-if="lastLogin != null" class="font-weight-bold">
              {{
                ui.formatString(T.userLastLogin, {
                  lastLogin: time.formatDateTime(lastLogin),
                })
              }}
            </label>
            <label v-else>
              {{ T.userNeverLoggedIn }}
            </label>
          </div>
          <div data-birth-date class="col-md">
            <label v-if="birthDate != null" class="font-weight-bold">
              {{
                ui.formatString(T.userBirthDate, {
                  birthDate: time.formatDate(birthDate),
                })
              }}
            </label>
          </div>
        </div>
        <div class="row mb-3">
          <form class="form w-100" @submit.prevent="onGenerateToken">
            <div class="col-md-12">
              <div class="input-group">
                <input
                  :value="link"
                  class="form-control"
                  name="link"
                  type="text"
                  :placeholder="T.passwordGenerateTokenDesc"
                  readonly
                  @focus="$event.target.select()"
                />
                <div class="input-group-append">
                  <button
                    v-clipboard="() => link"
                    :disabled="!link"
                    class="btn btn-outline-secondary"
                    name="copy"
                    type="button"
                    data-copy-to-clipboard
                    :aria-label="T.passwordCopyToken"
                    :title="T.passwordCopyToken"
                    @click.prevent="
                      ui.success(T.passwordResetLinkCopiedToClipboard)
                    "
                  >
                    <font-awesome-icon icon="clipboard" />
                  </button>
                  <button
                    class="btn btn-outline-secondary"
                    type="button"
                    :title="T.passwordGenerateTokenDesc"
                    @click.prevent="onGenerateToken"
                  >
                    {{ T.passwordGenerateToken }}
                  </button>
                </div>
              </div>
            </div>
          </form>
        </div>
        <div class="row mb-3">
          <form class="form w-100" @submit.prevent="onUpdateEmail">
            <div class="col-md-12">
              <div class="input-group">
                <input
                  v-model="newEmail"
                  class="form-control"
                  name="new_email"
                  type="text"
                  required="required"
                  :placeholder="T.adminSupportTypeNewEmail"
                />
                <div class="input-group-append">
                  <button
                    class="btn btn-outline-secondary"
                    type="submit"
                    :title="T.adminSupportTypeNewEmail"
                  >
                    {{ T.wordsSaveChanges }}
                  </button>
                </div>
              </div>
            </div>
          </form>
        </div>
        <div class="row mb-3">
          <h4>{{ T.supportAssignUserRoles }}</h4>
          <table class="table">
            <tbody>
              <tr v-for="role in roleNamesWithDescription" :key="role.name">
                <td>
                  <input
                    v-if="role.name != 'Admin'"
                    type="checkbox"
                    :checked="hasRole(role.name)"
                    :class="role.name"
                    @change.prevent="onChangeRole($event, role)"
                  />
                </td>
                <td>
                  <span class="badge badge-info w-100">{{ role.name }}</span>
                </td>
                <td>{{ role.description }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </template>
    </div>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop, Emit, Watch } from 'vue-property-decorator';
import Clipboard from 'v-clipboard';
import T from '../../lang';
import * as ui from '../../ui';
import * as time from '../../time';
import { omegaup } from '../../omegaup';
import { types } from '../../api_types';
import omegaup_ToggleSwitch from '../ToggleSwitch.vue';

import {
  FontAwesomeIcon,
  FontAwesomeLayers,
  FontAwesomeLayersText,
} from '@fortawesome/vue-fontawesome';
import { fas } from '@fortawesome/free-solid-svg-icons';
import { library } from '@fortawesome/fontawesome-svg-core';
library.add(fas);
Vue.use(Clipboard);

export interface UpdateEmailRequest {
  email: string;
  newEmail: string;
}

export enum MaintenanceType {
  Info = 'info',
  Warning = 'warning',
  Error = 'danger',
}

@Component({
  components: {
    'font-awesome-icon': FontAwesomeIcon,
    'font-awesome-layers': FontAwesomeLayers,
    'font-awesome-layers-text': FontAwesomeLayersText,
    'omegaup-toggle-switch': omegaup_ToggleSwitch,
  },
})
export default class AdminSupport extends Vue {
  @Prop() username!: string;
  @Prop() email!: string;
  @Prop() verified!: boolean;
  @Prop() link!: string;
  @Prop() lastLogin!: null | Date;
  @Prop() birthDate!: null | Date;
  @Prop() roles!: string[];
  @Prop() roleNamesWithDescription!: types.UserRole[];

  @Prop() contestAlias!: string;
  @Prop() contestTitle!: string;
  @Prop() contestFound!: boolean;
  @Prop() isContestRecommended!: boolean;
  @Prop() maintenanceEnabled!: boolean;
  @Prop() maintenanceMessageEs!: string;
  @Prop() maintenanceMessageEn!: string;
  @Prop() maintenanceMessagePt!: string;
  @Prop() maintenanceType!: string;

  currentContestAlias = this.contestAlias;
  currentIsContestRecommended = this.isContestRecommended;
  currentMaintenanceEnabled = this.maintenanceEnabled;
  currentMaintenanceMessageEs = this.maintenanceMessageEs;
  currentMaintenanceMessageEn = this.maintenanceMessageEn;
  currentMaintenanceMessagePt = this.maintenanceMessagePt;
  currentMaintenanceType = this.maintenanceType || 'info';

  T = T;
  ui = ui;
  time = time;
  MaintenanceType = MaintenanceType;
  usernameOrEmail: null | string = null;
  newEmail: null | string = null;

  hasRole(role: string): boolean {
    return this.roles.indexOf(role) !== -1;
  }

  @Emit('search-username-or-email')
  onSearchEmail(): null | string {
    if (this.usernameOrEmail == null) return null;
    return this.usernameOrEmail;
  }

  @Emit('update-email')
  onUpdateEmail(): null | UpdateEmailRequest {
    if (this.email == null || this.newEmail == null) return null;
    return { email: this.email, newEmail: this.newEmail };
  }

  @Emit('verify-user')
  onVerifyUser(): null | string {
    if (this.usernameOrEmail == null) return null;
    return this.usernameOrEmail;
  }

  @Emit('generate-token')
  onGenerateToken(): null | string {
    if (this.email == null) return null;
    return this.email;
  }

  @Emit('reset')
  onReset() {
    this.usernameOrEmail = null;
    this.newEmail = null;
  }

  @Emit('change-role')
  onChangeRole(
    ev: Event,
    role: types.UserRole,
  ): omegaup.Selectable<types.UserRole> {
    return {
      value: role,
      selected: (ev.target as HTMLInputElement).checked,
    };
  }

  @Emit('search-contest')
  onSearchContest(): null | string {
    if (this.currentContestAlias == null) return null;
    return this.currentContestAlias;
  }

  @Emit('toggle-recommended')
  onToggleRecommended(): boolean {
    return this.currentIsContestRecommended;
  }

  @Emit('reset-contest')
  onResetContest(): void {
    // The actual reset will be handled in support.ts
  }

  @Watch('isContestRecommended')
  onContestRecommendedChange(newValue: boolean) {
    this.currentIsContestRecommended = newValue;
  }

  @Watch('contestAlias')
  onContestAliasChange(newValue: string) {
    this.currentContestAlias = newValue;
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
    }
    return newValue;
  }

  @Emit('save-maintenance')
  onSaveMaintenance(): {
    enabled: boolean;
    message_es: string;
    message_en: string;
    message_pt: string;
    type: string;
  } {
    return {
      enabled: this.currentMaintenanceEnabled,
      message_es: this.currentMaintenanceMessageEs,
      message_en: this.currentMaintenanceMessageEn,
      message_pt: this.currentMaintenanceMessagePt,
      type: this.currentMaintenanceType,
    };
  }
}
</script>
