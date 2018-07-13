<template>
  <div class="panel panel-primary">
    <div class="panel-body">
      <form class="form"
            v-on:submit.prevent="onSubmit">
        <div class="form-group">
          <label>{{T.wordsGroupAdmin}}</label> <omegaup-autocomplete v-bind:init=
          "el =&gt; UI.typeahead(el, API.Group.list)"
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
            <a v-bind:href="`/group/${group.alias}/edit/`"></a>{{group.name}}
          </td>
          <td>{{group.role}}</td>
          <td><button class="close"
                  type="button"
                  v-if="group.name != 'admin'">&times;</button></td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script>
import {T, UI, API} from '../../omegaup.js';
import Autocomplete from '../Autocomplete.vue';

export default {
  props: {groupAdmins: Array},
  data: function() {
    return { T: T, UI: UI, API: API, groupName: "" }
  },
  methods: {onSubmit: function() { this.$parent.$emit('add-group-admin', this);}},
  components: {'omegaup-autocomplete': Autocomplete}
}
</script>
