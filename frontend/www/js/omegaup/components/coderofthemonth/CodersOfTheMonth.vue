<template>
  <div class="panel panel-default">
    <div class="panel-body">
      <table class="table table-striped table-hover">
        <thead>
          <tr>
            <th></th>
            <th>{{T.codersOfTheMonthCountry}}</th>
            <th>{{T.codersOfTheMonthUser}}</th>
            <th>{{T.profileStatisticsNumberOfSolvedProblems}}</th>
            <th>{{T.rankScore}}</th>
            <th>{{T.wordsActions}}</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="coder in coders">
            <td><img v-bind:src="coder.gravatar_32"></td>
            <td><omegaup-countryflag v-bind:country="coder.country_id"></omegaup-countryflag></td>
            <td><omegaup-user-username v-bind:classname="coder.classname"
                                   v-bind:linkify="true"
                                   v-bind:username="coder.username"></omegaup-user-username></td>
            <td>{{coder.ProblemsSolved}}</td>
            <td>{{coder.score}}</td>
            <td><button class="btn btn-primary"
                    v-if="canChooseCoder">{{T.coderOfTheMonthChooseAsCoder}}</button></td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script>
import {T} from '../../omegaup.js';
import user_Username from '../user/Username.vue';
import country_Flag from '../CountryFlag.vue';

export default {
  props: {coders: Array, canChooseCoder: Boolean},
  data: function() {
    return {
      T: T,
    };
  },
  components: {
    'omegaup-countryflag': country_Flag,
    'omegaup-user-username': user_Username,
  }
};
</script>
