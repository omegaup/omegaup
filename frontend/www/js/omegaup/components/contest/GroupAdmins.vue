<template>
  <div class="panel panel-primary">
    <div class="panel-body">
      <form class="form"
            v-on:submit.prevent="onSubmit">
        <div class="form-group">
          <label>{{T.wordsGroupAdmin}}</label> <omegaup-autocomplete v-bind:init=
          "el =&gt; UI.groupTypeahead(el)"
               v-model="groupName"></omegaup-autocomplete>
        </div><button class="btn btn-primary"
              type="submit">{{T.contestAddgroupAddGroup}}</button>
      </form>
    </div>
    <table class="table table-striped">
      <thead>
        <tr>
          <th>{{T.contestEditRegisteredGroupAdminName}}</th>
          <th>{{T.contestEditRegisteredAdminRole}}</th>
          <th>{{T.contestEditRegisteredAdminDelete}}</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="group in groupAdmins">
          <td>
            <a v-bind:href="`/group/${group.alias}/edit/`">{{group.name}}</a>
          </td>
          <td>{{group.role}}</td>
          <td><button class="close"
                  type="button"
                  v-if="group.name != 'admin'"
                  v-on:click="onRemove(group)">×</button></td>
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
export default class GroupAdmin extends Vue {
  @Prop() data!: omegaup.ContestGroupAdmin[];

  T = T;
  UI = UI;
  groupName = '';
  groupAdmins = this.data;
  selected = {};

  onSubmit(): void {
    this.$parent.$emit('add-group-admin', this);
  }

  onRemove(group: omegaup.ContestGroupAdmin): void {
    this.selected = group;
    this.$parent.$emit('remove-group-admin', this);
  }
}

</script>
