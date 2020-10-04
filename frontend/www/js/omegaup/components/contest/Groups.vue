<template>
  <div class="panel panel-primary">
    <div class="panel-body">
      <form class="form" @submit.prevent="onSubmit">
        <div class="form-group">
          <label>{{ T.wordsGroup }}</label>
          <omegaup-autocomplete
            v-model="groupName"
            :init="(el) => typeahead.groupTypeahead(el)"
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
            <a :href="`/group/${group.alias}/edit/`">{{ group.name }}</a>
          </td>
          <td>
            <button class="close" type="button" @click="onRemove(group)">
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
export default class Groups extends Vue {
  @Prop() data!: omegaup.ContestGroup[];

  T = T;
  typeahead = typeahead;
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
