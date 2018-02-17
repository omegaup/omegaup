<template>
  <div class="omegaup-user-roles panel-primary panel">
    <div class="panel-heading">
      <h2 class="panel-title">{{ T.omegaupTitleUpdatePrivileges }}</h2>
    </div>
    <div class="panel-body">
      <h4>{{ T.userRoles }}</h4>
      <table class="table">
        <tbody>
          <tr v-for="role in roleNames">
            <td><input type="checkbox"
                   v-bind:checked="hasRole(role)"
                   v-on:change.prevent="onChangeRole($event, role)"></td>
            <td>{{ role }}</td>
          </tr>
        </tbody>
      </table>
      <h4>{{ T.userGroups }}</h4>
      <table class="table">
        <tbody>
          <tr v-for="group in groupNames">
            <td><input type="checkbox"
                   v-bind:checked="isInGroup(group)"
                   v-on:change.prevent="onChangeGroup($event, group)"></td>
            <td>{{ group }}</td>
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
    roleNames: Array,
    groupNames: Array,
    roles: Array,
    groups: Array,
  },
  data: function() {
    return {
      T: T,
      password: '',
    };
  },
  methods: {
    hasRole: function(name) { return this.roles.indexOf(name) !== -1;},
    onChangeRole: function(ev, role) {
      this.$emit('change-role', role, ev.target.checked);
    },
    isInGroup: function(name) { return this.groups.indexOf(name) !== -1;},
    onChangeGroup: function(ev, group) {
      this.$emit('change-group', group, ev.target.checked);
    },
  },
};
</script>
