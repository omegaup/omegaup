import Vue from 'vue';
import problem_Mine from '../components/problem/Mine.vue';
import { OmegaUp } from '../omegaup';
import { types } from '../api_types';
import { omegaup } from '../omegaup';
import T from '../lang';
import * as api from '../api';
import * as UI from '../ui';

OmegaUp.on('ready', () => {
  const payload = types.payloadParsers.ProblemsMineInfoPayload();
  let showAllProblems = false;
  const problemsMine = new Vue({
    el: '#main-container',
    render: function(createElement) {
      return createElement('omegaup-problem-mine', {
        props: {
          problems: this.problems,
          privateProblemsAlert: payload.privateProblemsAlert,
          isSysadmin: payload.isSysadmin,
          pagerItems: this.pagerItems,
        },
        on: {
          'change-show-all-problems': (shouldShowAll: boolean) => {
            showAllProblems = shouldShowAll;
            showProblems(shouldShowAll);
          },
          'change-visibility': (
            selectedProblems: string[],
            visibility: number,
          ) => {
            Promise.all(
              selectedProblems.map((problemAlias: string) =>
                api.Problem.update({
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
          'go-to-page': (pageNumber: number) => {
            if (pageNumber > 0) {
              showProblems(showAllProblems, pageNumber);
            }
          },
        },
      });
    },
    data: {
      problems: {},
      pagerItems: <types.PageItem[]>[],
    },
    components: {
      'omegaup-problem-mine': problem_Mine,
    },
  });

  function showProblems(showAllProblems: boolean, pageNumber?: number): void {
    (showAllProblems
      ? api.Problem.adminList({
          page: pageNumber,
        })
      : api.Problem.myList({
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
