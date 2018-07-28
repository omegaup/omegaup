<template>
  <div class="wait_for_ajax panel panel-default">
    <div class="panel-heading">
      <ul class="nav nav-tabs">
        <li class="active"
            v-on:click="showCurrentMonth = true">
          <a data-toggle="tab">{{T.codersOfTheMonth}}</a>
        </li>
        <li v-on:click="showCurrentMonth = false">
          <a data-toggle="tab">{{T.codersOfTheMonthList}}</a>
        </li>
      </ul>
    </div>
    <div class="panel-body"></div>
    <table class="table table-striped table-hover">
      <thead>
        <tr>
          <th></th>
          <th>{{T.codersOfTheMonthCountry}}</th>
          <th>{{T.codersOfTheMonthUser}}</th>
          <th v-if="showCurrentMonth">{{T.codersOfTheMonthDate}}</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="coder in visibleCoders">
          <td><img v-bind:src="coder.gravatar_32"></td>
          <td><img v-bind:src="`/media/flags/${coder.country_id.toLowerCase()}.png`"
               v-if="coder.country_id != null"></td>
          <td><omegaup-user-username v-bind:classname="coder.classname"
                                 v-bind:linkify="true"
                                 v-bind:username="coder.username"></omegaup-user-username></td>
          <td v-if="showCurrentMonth">{{coder.date}}</td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script>
import {API, T} from '../../omegaup.js';
import UI from '../../ui.js';
import user_Username from '../user/Username.vue';

export default {
  props: {},
  computed: {
    visibleCoders: function() {
      return this.showCurrentMonth ? this.codersOfCurrentMonth :
                                     this.codersOfPreviousMonth;
    },
  },
  data: function() {
    return {
      T: T,
      UI: UI,
      codersOfCurrentMonth: [],
      codersOfPreviousMonth: [],
      showCurrentMonth: true
    };
  },
  created: function() {
    // top coder of the month
    var self = this;
    API.User.coderOfTheMonthList()
        .then(function(response) {
          self.codersOfCurrentMonth = response.coders;
        })
        .fail(UI.apiError);

    // coder of the month list
    var today = new Date();
    API.User.coderOfTheMonthList({date: today.format('{yyyy}-{MM}-{dd}')})
        .then(function(response) {
          self.codersOfPreviousMonth = response.coders;
        })
        .fail(UI.apiError);
  },
  components: {
    'omegaup-user-username': user_Username,
  }
};
</script>
