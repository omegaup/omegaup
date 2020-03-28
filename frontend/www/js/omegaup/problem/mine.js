import Vue from 'vue';
import problem_Mine from '../components/problem/Mine.vue';
import { OmegaUp } from '../omegaup';
import T from '../lang';
import API from '../api.js';
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
          pagerItems: this.pagerItems,
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
          'go-to-page': pageNumber => {
            if (pageNumber > 0) {
              showProblems(showAllProblems, pageNumber);
            }
          },
        },
      });
    },
    data: {
      problems: null,
      privateProblemsAlert: payload.privateProblemsAlert,
      isSysadmin: payload.isSysadmin,
      pagerItems: [],
    },
    components: {
      'omegaup-problem-mine': problem_Mine,
    },
  });

  function showProblems(showAllProblems, pageNumber) {
    (showAllProblems
      ? API.Problem.adminList({
          page: pageNumber,
        })
      : API.Problem.myList({
          page: pageNumber,
        })
    )
      .then(result => {
        problemsMine.pagerItems = result.pagerItems;
        problemsMine.problems = result.problems;
      })
      .catch(UI.apiError);
  }

  showProblems(showAllProblems, /*pageNumber=*/ 1);
});
