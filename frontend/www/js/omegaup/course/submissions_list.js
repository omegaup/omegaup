import course_SubmissionsList from '../components/activity/SubmissionsList.vue';
import {API, UI, OmegaUp, T} from '../omegaup.js';
import Vue from 'vue';

let course_alias =
    /\/course\/([^\/]+)\/list\/?/.exec(window.location.pathname)[1];

OmegaUp.on('ready', function() {
  $.when(API.Course.listSolvedProblems({course_alias: course_alias}),
         API.Course.listUnsolvedProblems({course_alias: course_alias}))
      .then((solvedProblems, unsolvedProblems) => {
        let submissionsList = new Vue({
          el: '#course-submissions-list',
          render: function(createElement) {
            return createElement('omegaup-course-submissions-list', {
              props: {
                solvedProblems: solvedProblems['user_problems'],
                unsolvedProblems: unsolvedProblems['user_problems'],
              },
            });
          },
          components: {
            'omegaup-course-submissions-list': course_SubmissionsList,
          },
        });
      })
      .fail(UI.apiError);
});
