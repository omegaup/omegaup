import Vue from 'vue';
import problem_Mine from '../components/problem/Mine.vue';
import { OmegaUp, T, API } from '../omegaup.js';
import UI from '../ui.js';

OmegaUp.on('ready', () => {
  const payload = JSON.parse(document.getElementById('payload').innerText);
  let showAllProblems = false;
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
          'change-show-all-problems': ev => {
            showAllProblems = ev.selected;
            showProblems(showAllProblems);
          },
          'change-visibility': (ev, selectedProblems, visibility) => {
            Promise.all(
              selectedProblems.map(problemAlias =>
                API.Problem.update({
                  problem_alias: problemAlias,
                  visibility: visibility,
                  message:
                    visibility === 1
                      ? 'private -> public'
                      : 'public -> private',
                }),
              ),
            )
              .then(() => {
                UI.success(T.updateItemsSuccess);
              })
              .catch(error => {
                UI.error(UI.formatString(T.bulkOperationError, error));
              })
              .finally(() => {
                showProblems(showAllProblems);
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
    .then(result => {
      problemsMine.problems = result.problems;
    })
    .catch(omegaup.UI.apiError);

  function showProblems(showAllProblems) {
    (showAllProblems ? API.Problem.adminList() : API.Problem.myList())
      .then(result => {
        problemsMine.problems = result.problems;
      })
      .catch(omegaup.UI.apiError);
  }
});
