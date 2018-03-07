<template>
  <div class="omegaup-user-roles panel-primary panel">
    <div class="panel-heading">
      <h2 class="panel-title">{{ T.omegaupTitleUpdatePrivileges }}</h2>
    </div>
    <div class="panel-body">
      <h4>{{ T.userRoles }}</h4>
      <table class="table">
        <tbody>
          <tr v-for="role in roles">
            <td><input type="checkbox"
                   v-model="role.value"
                   v-on:change.prevent="onChangeRole($event, role.title)"></td>
            <td>{{ role.title }}</td>
          </tr>
        </tbody>
      </table>
      <h4>{{ T.userGroups }}</h4>
      <table class="table">
        <tbody>
          <tr v-for="group in groups">
            <td><input type="checkbox"
                   v-model="group.value"
                   v-on:change.prevent="onChangeGroup($event, group.title)"></td>
            <td>{{ group.title }}</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script>
import {T} from '../../omegaup.js';

export default {
  props: {
    initialRoles: Array,
    initialGroups: Array,
  },
  data: function() {
    return {T: T, roles: this.initialRoles, groups: this.initialGroups};
  },
  methods: {
    onChangeRole: function(ev, role) {
      this.$emit('change-role', role, ev.target.checked);
    },
    onChangeGroup: function(ev, group) {
      this.$emit('change-group', group, ev.target.checked);
    },
  },
};
</script>
