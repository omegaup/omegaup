import course_SubmissionsList from '../components/course/SubmissionsList.vue';
import {API, UI, OmegaUp, T} from '../omegaup.js';
import Vue from 'vue';

let course_alias =
    /\/course\/([^\/]+)\/list\/?/.exec(window.location.pathname)[1];

OmegaUp.on('ready', function() {
  var submissionsList = new Vue({
    el: '#course-submissions-list',
    render: function(createElement) {
      return createElement('omegaup-course-submissions-list', {
        props: {
          solvedProblems: this.solvedProblems,
          unsolvedProblems: this.unsolvedProblems,
        },
      });
    },
    mounted: function() {
      API.Course.listSolvedProblems({course_alias: course_alias})
          .then(function(data) {
            submissionsList.solvedProblems = data['user_problems'];
          })
          .fail(UI.apiError);

      API.Course.listUnsolvedProblems({course_alias: course_alias})
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
      'omegaup-course-submissions-list': course_SubmissionsList,
    },
  });
});
