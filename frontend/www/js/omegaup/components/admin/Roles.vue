<template>
  <div class="card">
    <h2 class="card-header text-white bg-primary">
      {{ T.omegaupTitleUpdatePrivileges }}
    </h2>
    <div class="card-body">
      <h4>{{ T.userRoles }}</h4>
      <table class="table">
        <tbody>
          <tr v-for="role in currentRoles" :key="role.name">
            <td>
              <input
                v-model="role.value"
                type="checkbox"
                @change.prevent="changeRole($event, role)"
              />
            </td>

            <td>{{ role.name }}</td>
          </tr>
        </tbody>
      </table>
      <h4>{{ T.userGroups }}</h4>
      <table class="table">
        <tbody>
          <tr v-for="group in currentGroups" :key="group.alias">
            <td>
              <input
                v-model="group.value"
                type="checkbox"
                @change.prevent="changeGroup($event, group)"
              />
            </td>

            <td>{{ group.name }}</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop, Emit } from 'vue-property-decorator';
import { omegaup } from '../../omegaup';
import { types } from '../../api_types';
import T from '../../lang';

@Component
export default class AdminRoles extends Vue {
  @Prop() roles!: types.UserRole[];
  @Prop() groups!: types.Group[];

  T = T;
  currentRoles: types.UserRole[] = this.roles;
  currentGroups: types.Group[] = this.groups;

  @Emit()
  changeRole(
    ev: Event,
    role: types.UserRole,
  ): omegaup.Selectable<types.UserRole> {
    return {
      value: role,
      selected: (ev.target as HTMLInputElement).checked,
    };
  }

  @Emit()
  changeGroup(ev: Event, group: types.Group): omegaup.Selectable<types.Group> {
    return {
      value: group,
      selected: (ev.target as HTMLInputElement).checked,
    };
  }
}
</script>
