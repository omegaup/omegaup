<template>
  <div class="wait_for_ajax panel panel-default">
    <div class="panel-heading">
      <ul class="nav nav-tabs">
        <li class="active"
            v-on:click="showCurrentMonth = 1">
          <a data-toggle="tab">{{T.codersOfTheMonth}}</a>
        </li>
        <li v-on:click="showCurrentMonth = 2">
          <a data-toggle="tab">{{T.codersOfTheMonthList}}</a>
        </li>
        <li v-if="isMentor"
            v-on:click="showCurrentMonth = 3">
          <a data-toggle="tab">{{T.codersOfTheMonthListCandidate}}</a>
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
          <th v-if="showCurrentMonth == 1">{{T.codersOfTheMonthDate}}</th>
          <th v-if="showCurrentMonth == 3">{{T.profileStatisticsNumberOfSolvedProblems}}</th>
          <th v-if="showCurrentMonth == 3">{{T.rankScore}}</th>
          <th v-if="showCurrentMonth == 3">{{T.wordsActions}}</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="coder in visibleCoders">
          <td><img v-bind:src="coder.gravatar_32"></td>
          <td><omegaup-countryflag v-bind:country="coder.country_id"></omegaup-countryflag></td>
          <td><omegaup-user-username v-bind:classname="coder.classname"
                                 v-bind:linkify="true"
                                 v-bind:username="coder.username"></omegaup-user-username></td>
          <td v-if="showCurrentMonth == 1">{{coder.date}}</td>
          <td v-if="showCurrentMonth == 3">{{coder.ProblemsSolved}}</td>
          <td v-if="showCurrentMonth == 3">{{coder.score}}</td>
          <td v-if="showCurrentMonth == 3"><button class="btn btn-primary"
                  v-if="canChooseCoder &amp;&amp; !coderIsSelected"
                  v-on:click=
                  "onSelectCoder(coder.username)">{{T.coderOfTheMonthChooseAsCoder}}</button></td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script>
import {T} from '../../omegaup.js';
import user_Username from '../user/Username.vue';
import country_Flag from '../CountryFlag.vue';

export default {
  props: {
    codersOfCurrentMonth: Array,
    codersOfPreviousMonth: Array,
    candidatesToCoderOfTheMonth: Array,
    canChooseCoder: Boolean,
    coderIsSelected: Boolean,
    isMentor: Boolean,
  },
  computed: {
    visibleCoders: function() {
      switch (this.showCurrentMonth) {
        case 1:
          return this.codersOfCurrentMonth;
        case 2:
          return this.codersOfPreviousMonth;
        case 3:
          return this.candidatesToCoderOfTheMonth;
        default:
          return this.codersOfCurrentMonth;
      }
    },
  },
  data: function() {
    return {
      T: T,
      showCurrentMonth: 1,
    };
  },
  methods: {
    onSelectCoder: function(coderUsername) {
      this.$emit('select-coder', coderUsername);
    },
  },
  components: {
    'omegaup-user-username': user_Username,
    'omegaup-countryflag': country_Flag,
  }
};
</script>
