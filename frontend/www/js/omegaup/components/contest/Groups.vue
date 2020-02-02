<template>
  <div class="panel panel-primary">
    <div class="panel-body">
      <form class="form" v-on:submit.prevent="onSubmit">
        <div class="form-group">
          <label>{{ T.wordsGroup }}</label>
          <omegaup-autocomplete
            v-bind:init="el => UI.groupTypeahead(el)"
            v-model="groupName"
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
          <th>{{ T.contestEditRegisteredAdminDelete }}</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="group in groups">
          <td>
            <a v-bind:href="`/group/${group.alias}/edit/`">{{ group.name }}</a>
          </td>
          <td>
            <button class="close" type="button" v-on:click="onRemove(group)">
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
import { T } from '../../omegaup.js';
import UI from '../../ui.js';
import omegaup from '../../api.js';
import Autocomplete from '../Autocomplete.vue';

@Component({
  components: {
    'omegaup-autocomplete': Autocomplete,
  },
})
export default class Groups extends Vue {
  @Prop() data!: omegaup.ContestGroup[];

  T = T;
  UI = UI;
  groupName = '';
  groups = this.data;
  selected: omegaup.ContestGroup | null = null;

  onSubmit(): void {
    this.$emit('emit-add-group', this);
  }

  onRemove(group: omegaup.ContestGroup): void {
    this.selected = group;
    this.$emit('emit-remove-group', this);
  }
}
</script>
