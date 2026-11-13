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
import { defineComponent } from 'vue';
import type { PropType } from 'vue';
import type { omegaup } from '../../omegaup';
import type { types } from '../../api_types';
import T from '../../lang';

export default defineComponent({
  name: 'AdminRoles',
  props: {
    roles: {
      type: Array as PropType<types.UserRole[]>,
      default: () => [],
    },
    groups: {
      type: Array as PropType<types.Group[]>,
      default: () => [],
    },
  },
  emits: ['change-role', 'change-group'],
  data(): {
    T: typeof T;
    currentRoles: types.UserRole[];
    currentGroups: types.Group[];
  } {
    return {
      T,
      currentRoles: this.roles,
      currentGroups: this.groups,
    };
  },
  methods: {
    changeRole(ev: Event, role: types.UserRole): void {
      this.$emit('change-role', {
        value: role,
        selected: (ev.target as HTMLInputElement).checked,
      } as omegaup.Selectable<types.UserRole>);
    },
    changeGroup(ev: Event, group: types.Group): void {
      this.$emit('change-group', {
        value: group,
        selected: (ev.target as HTMLInputElement).checked,
      } as omegaup.Selectable<types.Group>);
    },
  },
});
</script>
