import contest_SubmissionsList from '../components/contest/SubmissionsList.vue';
import {API, UI, OmegaUp, T} from '../omegaup.js';
import Vue from 'vue';

let contest_alias =
    /\/contest\/([^\/]+)\/list\/?/.exec(window.location.pathname)[1];

OmegaUp.on('ready', function() {
  var submissionsList = new Vue({
    el: '#contest-submissions-list',
    render: function(createElement) {
      return createElement('omegaup-contest-submissions-list', {
        props: {
          solvedProblems: this.solvedProblems,
          unsolvedProblems: this.unsolvedProblems,
        },
      });
    },
    mounted: function() {
      API.Contest.listSolvedProblems({contest_alias: contest_alias})
          .then(function(data) {
            submissionsList.solvedProblems = data['user_problems'];
          })
          .fail(UI.apiError);

      API.Contest.listUnsolvedProblems({contest_alias: contest_alias})
          .then(function(data) {
            submissionsList.unsolvedProblems = data['user_problems'];
          })
          .fail(UI.apiError);
    },
    data: {
      solvedProblems: undefined,
      unsolvedProblems: undefined,
    },
    components: {
      'omegaup-contest-submissions-list': contest_SubmissionsList,
    },
  });
});
