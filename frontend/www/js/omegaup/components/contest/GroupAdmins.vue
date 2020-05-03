<template>
  <div class="panel panel-primary">
    <div class="panel-body">
      <form class="form" v-on:submit.prevent="onSubmit">
        <div class="form-group">
          <label>{{ T.wordsGroupAdmin }}</label>
          <omegaup-autocomplete
            v-bind:init="el => typeahead.groupTypeahead(el)"
            v-bind:value.sync="groupAlias"
          ></omegaup-autocomplete>
        </div>
        <button class="btn btn-primary" type="submit">
          {{ T.contestAddgroupAddGroup }}
        </button>
      </form>
    </div>
    <table class="table table-striped">
      <thead>
        <tr>
          <th>{{ T.contestEditRegisteredGroupAdminName }}</th>
          <th>{{ T.contestEditRegisteredAdminRole }}</th>
          <th>{{ T.contestEditRegisteredAdminDelete }}</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="group in groupAdmins">
          <td>
            <a v-bind:href="`/group/${group.alias}/edit/`">{{ group.name }}</a>
          </td>
          <td>{{ group.role }}</td>
          <td>
            <button
              class="close"
              type="button"
              v-if="group.name != 'admin'"
              v-on:click="onRemove(group)"
            >
              ×
            </button>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import { omegaup } from '../../omegaup';
import T from '../../lang';
import * as typeahead from '../../typeahead';
import Autocomplete from '../Autocomplete.vue';

@Component({
  components: {
    'omegaup-autocomplete': Autocomplete,
  },
})
export default class GroupAdmin extends Vue {
  @Prop() data!: omegaup.ContestGroupAdmin[];

  T = T;
  typeahead = typeahead;
  groupAlias = '';
  groupAdmins = this.data;
  selected: omegaup.ContestGroupAdmin = {};

  onSubmit(): void {
    this.$emit('emit-add-group-admin', this);
  }

  onRemove(group: omegaup.ContestGroupAdmin): void {
    this.selected = group;
    this.$emit('emit-remove-group-admin', this);
  }
}
</script>
