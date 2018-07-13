<template>
  <div>
    <div class="panel panel-primary">
      <div class="panel-body">
        <form class="form"
              v-on:submit.prevent="onSubmit">
          <div class="form-group">
            <label>{{T.wordsUser}}</label>
            <omegaup-autocomplete v-model="contestant" v-bind:init="el => UI.userTypeahead(el)"></omegaup-autocomplete>
          </div><button class="btn btn-primary user-add-single"
                type="submit">{{T.contestAdduserAddUser}}</button>
          <hr>
          <div class="form-group">
            <label>{{T.wordsMultipleUser}}</label>
            <textarea class="form-control"
                 rows="4"
                 v-model="contestants"></textarea>
          </div><button class="btn btn-primary user-add-bulk"
                type="submit">{{T.contestAdduserAddUsers}}</button>
        </form>
      </div>
      <table class="table table-striped">
        <thead>
          <tr>
            <th>{{T.wordsUser}}</th>
            <th>{{T.contestAdduserRegisteredUserTime}}</th>
            <th>{{T.contestAdduserRegisteredUserDelete}}</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="user in users">
            <td>
              <a v-bind:href="`/profile/${user.username}/`">{{user.username}}</a>
            </td>
            <td>{{user.access_time}}</td>
            <td><button class="close"
                    type="button">x</button></td>
          </tr>
        </tbody>
      </table>
    </div>
    <div class="panel panel-primary">
      <div class="panel-body">
        {{T.pendingRegistrations}}
      </div>
      <table class="table table-striped">
        <thead>
          <tr>
            <th>{{T.wordsUser}}</th>
            <th>{{T.userEditCountry}}</th>
            <th>{{T.requestDate}}</th>
            <th>{{T.currentStatus}}</th>
            <th>{{T.lastUpdate}}</th>
            <th>{{T.contestAdduserAddContestant}}</th>
          </tr>
        </thead>
        <tbody></tbody>
      </table>
    </div>
  </div>
</template>

<script>
import {T, UI} from '../../omegaup.js';
import Autocomplete from '../Autocomplete.vue';

export default {
  props: {users: Array},
  data: function() {
    return { T: T, UI: UI, contestant: "", contestants: "" }
  },
  methods: {onSubmit: function() { this.$parent.$emit('addUser', this);}},
  components: {'omegaup-autocomplete': Autocomplete}
}
</script>
