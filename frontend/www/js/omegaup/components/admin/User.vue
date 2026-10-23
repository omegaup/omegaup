<template>
  <div class="omegaup-admin-user card">
    <div class="card-header">
      <h2 class="card-title">
        {{ T.omegaupTitleAdminUsers }} — {{ username }}
      </h2>
    </div>
    <div class="card-body">
      <form class="form bottom-margin" @submit.prevent="onChangePassword">
        <div class="row">
          <div class="col-md-12">
            <button
              class="btn"
              :class="{ 'btn-primary': !verified, 'btn-light': verified }"
              type="button"
              :disabled="verified"
              @click.prevent="onVerifyUser"
            >
              <span v-if="verified">
                <font-awesome-icon
                  icon="check-circle"
                  :style="{ color: 'green' }"
                />
                {{ T.userVerified }}</span
              ><span v-else>{{ T.userVerify }}</span>
            </button>
          </div>
        </div>
      </form>
      <h4>{{ T.userEmails }}</h4>
      <ul class="list-group">
        <li v-for="email in emails" :key="email" class="list-group-item">
          {{ email }}
        </li>
      </ul>
      <h4>{{ T.userRoles }}</h4>
      <table class="table">
        <tbody>
          <tr v-for="role in roleNames" :key="role.name">
            <td>
              <input
                type="checkbox"
                :checked="hasRole(role.name)"
                :disabled="role == 'Admin'"
                :class="role.name"
                @change.prevent="onChangeRole($event, role)"
              />
            </td>

            <td>{{ role.name }}</td>
          </tr>
        </tbody>
      </table>
      <h4>{{ T.wordsExperiments }}</h4>
      <table class="table">
        <tbody>
          <tr v-for="experiment in systemExperiments" :key="experiment.name">
            <td>
              <input
                type="checkbox"
                :checked="experiment.config || hasExperiment(experiment.name)"
                :disabled="experiment.config"
                @change.prevent="onChangeExperiment($event, experiment)"
              />
            </td>

            <td>{{ experiment.name }}</td>
            <td>{{ experiment.hash }}</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import type { PropType } from 'vue';
import type { omegaup } from '../../omegaup';
import T from '../../lang';

import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { fas } from '@fortawesome/free-solid-svg-icons';
import { library } from '@fortawesome/fontawesome-svg-core';
import type { types } from '../../api_types';
library.add(fas);

export default defineComponent({
  name: 'AdminUser',
  components: {
    'font-awesome-icon': FontAwesomeIcon,
  },
  props: {
    emails: {
      type: Array as PropType<string[]>,
      default: () => [],
    },
    username: {
      type: String,
      required: true,
    },
    verified: {
      type: Boolean,
      default: false,
    },
    experiments: {
      type: Array as PropType<string[]>,
      default: () => [],
    },
    systemExperiments: {
      type: Array as PropType<omegaup.Experiment[]>,
      default: () => [],
    },
    roles: {
      type: Array as PropType<string[]>,
      default: () => [],
    },
    roleNames: {
      type: Array as PropType<types.UserRole[]>,
      default: () => [],
    },
  },
  emits: ['change-experiment', 'change-role', 'verify-user'],
  data(): {
    T: typeof T;
  } {
    return {
      T,
    };
  },
  methods: {
    hasExperiment(experiment: string): boolean {
      return this.experiments.indexOf(experiment) !== -1;
    },
    hasRole(role: string): boolean {
      return this.roles.indexOf(role) !== -1;
    },
    onChangeExperiment(ev: Event, experiment: omegaup.Experiment): void {
      this.$emit('change-experiment', {
        value: experiment,
        selected: (ev.target as HTMLInputElement).checked,
      } as omegaup.Selectable<omegaup.Experiment>);
    },
    onChangeRole(ev: Event, role: types.UserRole): void {
      this.$emit('change-role', {
        value: role,
        selected: (ev.target as HTMLInputElement).checked,
      } as omegaup.Selectable<types.UserRole>);
    },
    onChangePassword(): void {
      // Legacy handler preserved for template form submit
    },
    onVerifyUser(): void {
      this.$emit('verify-user');
    },
  },
});
</script>
