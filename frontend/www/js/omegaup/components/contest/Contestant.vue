<template>
  <div>
    <div class="panel panel-primary contestants-input-area">
      <div class="panel-body">
        <form class="form"
              v-on:submit.prevent="onSubmit">
          <div class="form-group">
            <label>{{T.wordsUser}}</label> <omegaup-autocomplete v-bind:init=
            "el =&gt; UI.userTypeahead(el)"
                 v-model="contestant"></omegaup-autocomplete>
          </div><button class="btn btn-primary user-add-single"
                type="submit">{{T.contestAdduserAddUser}}</button>
          <hr>
          <div class="form-group">
            <label>{{T.wordsMultipleUser}}</label>
            <textarea class="form-control contestants"
                 rows="4"
                 v-model="contestants"></textarea>
          </div><button class="btn btn-primary user-add-bulk"
                type="submit">{{T.contestAdduserAddUsers}}</button>
        </form>
      </div>
      <table class="table table-striped participants">
        <thead>
          <tr>
            <th>{{T.wordsUser}}</th>
            <th>{{T.contestAdduserRegisteredUserTime}}</th>
            <th>{{T.contestAdduserRegisteredUserDelete}}</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="user in users">
            <td><omegaup-user-username v-bind:linkify="true"
                                   v-bind:username="user.username"></omegaup-user-username></td>
            <td>{{user.access_time}}</td>
            <td><button class="close"
                    type="button"
                    v-on:click="onRemove(user)">×</button></td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script>
import {T, UI} from '../../omegaup.js';
import Autocomplete from '../Autocomplete.vue';
import user_Username from '../user/Username.vue';

export default {
  props: {
    data: Array,
  },
  data: function() {
    return {
      T: T,
      UI: UI,
      contestant: '',
      contestants: '',
      users: this.data,
      selected: {},
    };
  },
  methods: {
    onSubmit: function() { this.$parent.$emit('add-user', this);},
    onRemove: function(user) {
      this.selected = user;
      this.$parent.$emit('remove-user', this);
    },
  },
  components: {
    'omegaup-autocomplete': Autocomplete,
    'omegaup-user-username': user_Username,
  },
};
</script>
