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
                   v-bind:checked="experiment.config || hasExperiment(experiment)"
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

<script>
import DatePicker from '../DatePicker.vue';
import {T} from '../../omegaup.js';

export default {
  props: {
    emails: Array,
    experiments: Array,
    systemExperiments: Array,
    roleNames: Array,
    roles: Array,
    username: String,
    verified: Boolean,
  },
  data: function() {
    return {
      T: T,
    };
  },
  methods: {
    hasExperiment: function(name) {
      return this.experiments.indexOf(name) !== -1;
    },
    hasRole: function(name) { return this.roles.indexOf(name) !== -1;},
    onChangeExperiment: function(ev, experiment) {
      this.$emit('change-experiment', experiment, ev.target.checked);
    },
    onChangeRole: function(ev, role) {
      this.$emit('change-role', role, ev.target.checked);
    },
    onVerifyUser: function() { this.$emit('verify-user');},
  },
};
</script>
