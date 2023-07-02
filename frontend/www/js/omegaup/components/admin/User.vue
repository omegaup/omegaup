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
                @change.prevent="onChangeRole($event, role)"
                :class="role.name"
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
import { Vue, Component, Prop, Emit } from 'vue-property-decorator';
import { omegaup } from '../../omegaup';
import T from '../../lang';

import {
  FontAwesomeIcon,
  FontAwesomeLayers,
  FontAwesomeLayersText,
} from '@fortawesome/vue-fontawesome';
import { fas } from '@fortawesome/free-solid-svg-icons';
import { library } from '@fortawesome/fontawesome-svg-core';
library.add(fas);

@Component({
  components: {
    'font-awesome-icon': FontAwesomeIcon,
    'font-awesome-layers': FontAwesomeLayers,
    'font-awesome-layers-text': FontAwesomeLayersText,
  },
})
export default class User extends Vue {
  @Prop() emails!: string[];
  @Prop() username!: string;
  @Prop() verified!: boolean;
  @Prop() experiments!: string[];
  @Prop() systemExperiments!: omegaup.Experiment[];
  @Prop() roles!: string[];
  @Prop() roleNames!: omegaup.Role[];

  T = T;

  hasExperiment(experiment: string): boolean {
    return this.experiments.indexOf(experiment) !== -1;
  }

  hasRole(role: string): boolean {
    return this.roles.indexOf(role) !== -1;
  }

  @Emit('change-experiment')
  onChangeExperiment(
    ev: Event,
    experiment: omegaup.Experiment,
  ): omegaup.Selectable<omegaup.Experiment> {
    return {
      value: experiment,
      selected: (ev.target as HTMLInputElement).checked,
    };
  }

  @Emit('change-role')
  onChangeRole(
    ev: Event,
    role: omegaup.Role,
  ): omegaup.Selectable<omegaup.Role> {
    return {
      value: role,
      selected: (ev.target as HTMLInputElement).checked,
    };
  }

  onVerifyUser() {
    this.$emit('verify-user');
  }
}
</script>
