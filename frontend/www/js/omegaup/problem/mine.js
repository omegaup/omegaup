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
          'change-visibility': function(ev, selectedProblems, visibility) {
            const promises = [];
            for (const problemAlias of selectedProblems) {
              promises.push(
                API.Problem.update({
                  problem_alias: problemAlias,
                  visibility: visibility,
                  message:
                    visibility === 1
                      ? 'private -> public'
                      : 'public -> private',
                }),
              );
            }

            Promise.all(promises)
              .then(() => {
                UI.success(T.updateItemsSuccess);
              })
              .catch(error => {
                UI.error(UI.formatString(T.bulkOperationError, error));
              })
              .finally(() => {
                showProblems(statement);
              });
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
    .catch(omegaup.UI.apiError);

  function showProblems(statement) {
    (statement ? API.Problem.adminList() : API.Problem.myList())
      .then(function(result) {
        problemsMine.problems = result.problems;
      })
      .catch(omegaup.UI.apiError);
  }
});
