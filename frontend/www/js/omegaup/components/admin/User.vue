<template>
  <div class="omegaup-admin-user panel-primary panel">
    <div class="panel-heading">
      <h2 class="panel-title">{{ T.omegaupTitleAdminUsers }} — {{ username }}</h2>
    </div>
    <div class="panel-body">
      <form class="form bottom-margin"
            v-on:submit.prevent="onChangePassword">
        <div class="row">
          <div class="col-md-12">
            <button class="btn btn-default btn-block"
                 type="button"
                 v-bind:disabled="verified"
                 v-on:click.prevent="onVerifyUser"><span v-if="verified"><span aria-hidden="true"
                  class="glyphicon glyphicon-ok"></span> {{ T.userVerified }}</span><span v-else=
                  "">{{ T.userVerify }}</span></button>
          </div>
        </div>
      </form>
      <h4>{{ T.userEmails }}</h4>
      <ul class="list-group">
        <li class="list-group-item"
            v-for="email in emails">{{ email }}</li>
      </ul>
      <h4>{{ T.userRoles }}</h4>
      <table class="table">
        <tbody>
          <tr v-for="role in roleNames">
            <td><input type="checkbox"
                   v-bind:checked="hasRole(role)"
                   v-bind:disabled="role == 'Admin'"
                   v-on:change.prevent="onChangeRole($event, role)"></td>
            <td>{{ role }}</td>
          </tr>
        </tbody>
      </table>
      <h4>{{ T.wordsExperiments }}</h4>
      <table class="table">
        <tbody>
          <tr v-for="experiment in systemExperiments">
            <td><input type="checkbox"
                   v-bind:checked="experiment.config"
                   v-bind:disabled="experiment.config"
                   v-on:change.prevent="onChangeExperiment($event, experiment)"></td>
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
import { T } from '../../omegaup.js';
import omegaup from '../../api.js';

@Component({})
export default class User extends Vue {
  @Prop() emails!: string[];
  @Prop() username!: string;
  @Prop() verified!: boolean;
  @Prop() experiments!: omegaup.Experiment[];
  @Prop() systemExperiments!: omegaup.Experiment[];
  @Prop() roles!: string[];
  @Prop() roleNames!: string[];

  T = T;

  hasRole(role: omegaup.Role): boolean {
    return this.roles.indexOf(role.title) !== -1;
  }

  @Emit('change-experiment')
  onChangeExperiment(
    ev: Event,
    experiment: omegaup.Experiment,
  ): omegaup.Experiment {
    let selectedExperiment: omegaup.Experiment = experiment;
    selectedExperiment['config'] = (<HTMLInputElement>ev.target).checked;
    return selectedExperiment;
  }

  @Emit('change-role')
  onChangeRole(ev: Event, role: string): omegaup.Role {
    const selectedRole: omegaup.Group = {
      title: role,
      value: (<HTMLInputElement>ev.target).checked,
    };
    return selectedRole;
  }

  onVerifyUser() {
    this.$emit('verify-user');
  }
}

</script>
