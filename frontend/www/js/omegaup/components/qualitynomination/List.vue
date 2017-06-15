<template>
  <div class="panel">
    <div class="page-header">
      <h1>{{ T.qualityNomination }}</h1>
    </div>
    <div class="pull-right"
         v-if="!myView">
      <label><input type="checkbox"
             v-model="showAll"> {{ T.qualityNominationShowAll }}</label>
    </div>
    <div>
      <table class="table table-striped">
        <thead>
          <tr>
            <td>{{ T.qualityNominationType }}</td>
            <td>{{ T.wordsAlias }}</td>
            <td>{{ T.wordsUser }}</td>
            <td>{{ T.wordsSubmissionDate }}</td>
            <td>{{ T.qualityNominationAssignedJudge }}</td>
            <td>{{ T.wordsStatus }}</td>
            <td><!-- view button --></td>
          </tr>
        </thead>
        <tbody>
          <tr v-for="nomination in visibleNominations">
            <td>{{ nomination.nomination }}</td>
            <td>
              <a v-bind:href="problemUrl(nomination.problem.alias)">{{ nomination.problem.title
              }}</a>
            </td>
            <td>
              <a v-bind:href="userUrl(nomination.nominator.username)">{{ nomination.nominator.user
              || nomination.nominator.username }}</a>
            </td>
            <td>{{ nomination.time.format('long') }}</td>
            <td><!-- TODO: Judges aren't returned from the API yet --></td>
            <td>{{ nomination.status }}</td>
            <td>
              <a v-bind:href="nominationDetailsUrl(nomination.qualitynomination_id)">{{
              T.wordsDetails }}</a>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script>
import {T} from '../../omegaup.js';
import UI from '../../ui.js';

export default {
  props: {
    nominations: Array,
    currentUser: String,
    myView: Boolean,
  },
  data: function() {
    return {
      showAll: true,
      T: T,
    };
  },
  computed: {
    visibleNominations: function() {
      var self = this;
      if (this.showAll) {
        return this.nominations;
      } else {
        return this.nominations.filter(function(nomination) {
          return nomination.nominator.username == self.currentUser;
        });
      }
    },
  },
  methods: {
    problemUrl: function(problemAlias) {
      return '/arena/problem/' + problemAlias + '/';
    },
    userUrl: function(username) { return '/profile/' + username + '/';},
    nominationDetailsUrl: function(nominationId) {
      return '/nomination/' + nominationId + '/';
    }
  }
};
</script>
