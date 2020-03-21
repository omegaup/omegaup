import Vue from 'vue';
import problem_Mine from '../components/problem/Mine.vue';
import { OmegaUp, T, API } from '../omegaup.js';
import UI from '../ui.js';

OmegaUp.on('ready', function() {
  const payload = JSON.parse(document.getElementById('payload').innerText);
  let statement = false;
  const problemsMine = new Vue({
    el: '#problem-mine',
    render: function(createElement) {
      return createElement('omegaup-problem-mine', {
        props: {
          problems: this.problems,
          privateProblemsAlert: this.privateProblemsAlert,
          isSysadmin: this.isSysadmin,
        },
        on: {
          'change-show-all-problems': function(ev) {
            statement = ev.selected;
            showProblems(statement);
          },
          'change-visibility': function(visibility) {
            omegaup.UI.bulkOperation(
              function(alias, resolve, reject) {
                omegaup.API.Problem.update({
                  problem_alias: alias,
                  visibility: visibility,
                  message:
                    visibility === 1
                      ? 'private -> public'
                      : 'public -> private',
                })
                  .then(resolve)
                  .fail(reject);
              },
              function() {
                showProblems(statement);
              },
            );
          },
        },
      });
    },
    data: {
      problems: null,
      privateProblemsAlert: payload.privateProblemsAlert,
      isSysadmin: payload.isSysadmin,
    },
    components: {
      'omegaup-problem-mine': problem_Mine,
    },
  });

  omegaup.API.Problem.myList()
    .then(function(result) {
      problemsMine.problems = result.problems;
    })
    .fail(omegaup.UI.apiError);

  function showProblems(statement) {
    const deferred = statement
      ? omegaup.API.Problem.adminList()
      : omegaup.API.Problem.myList();
    deferred
      .then(function(result) {
        problemsMine.problems = result.problems;
      })
      .fail(omegaup.UI.apiError);
  }
});
