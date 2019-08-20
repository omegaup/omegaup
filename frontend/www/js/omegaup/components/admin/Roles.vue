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

<script lang="ts">
import { Vue, Component, Prop, Emit } from 'vue-property-decorator';
import { T } from '../../omegaup.js';
import omegaup from '../../api.js';

@Component({})
export default class AdminRoles extends Vue {
  @Prop() initialRoles!: omegaup.Role[];
  @Prop() initialGroups!: omegaup.Group[];

  T = T;
  roles: omegaup.Role[] = this.initialRoles;
  groups: omegaup.Group[] = this.initialGroups;

  @Emit()
  onChangeRole(ev: Event, role: string): omegaup.Role {
    return {
      title: role,
      value: (<HTMLInputElement>ev.target).checked,
    };
  }

  @Emit()
  onChangeGroup(ev: Event, group: string): omegaup.Group {
    const selectedGroup: omegaup.Group = {
      title: group,
      value: (<HTMLInputElement>ev.target).checked,
    };
    return selectedGroup;
  }
}

</script>
