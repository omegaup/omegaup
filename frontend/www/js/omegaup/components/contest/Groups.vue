<template>
  <div class="card mt-3">
    <div class="card-body">
      <form class="form" @submit.prevent="$emit('emit-add-group', groupName)">
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
    <table class="table table-striped mb-0">
      <thead>
        <tr>
          <th class="text-center">
            {{ T.contestEditRegisteredGroupAdminName }}
          </th>
          <th class="text-center">{{ T.contestEditRegisteredAdminDelete }}</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="group in groups" :key="group.alias">
          <td>
            <a :href="`/group/${group.alias}/edit/`">{{ group.name }}</a>
          </td>
          <td class="text-center">
            <button
              class="close float-none"
              type="button"
              @click="$emit('emit-remove-group', group.alias)"
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
import { Vue, Component, Prop, Watch } from 'vue-property-decorator';
import { types } from '../../api_types';
import T from '../../lang';
import * as typeahead from '../../typeahead';
import Autocomplete from '../Autocomplete.vue';

@Component({
  components: {
    'omegaup-autocomplete': Autocomplete,
  },
})
export default class Groups extends Vue {
  @Prop() groups!: types.ContestGroup[];

  T = T;
  typeahead = typeahead;
  groupName = '';
  selected: types.ContestGroup | null = null;

  @Watch('groups')
  onGroupsChange(): void {
    this.groupName = '';
    this.selected = null;
  }
}
</script>
