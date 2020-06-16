import Vue from 'vue';
import problem_Mine from '../components/problem/Mine.vue';
import { OmegaUp } from '../omegaup';
import { types } from '../api_types';
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
          visibilityStatuses: payload.visibilityStatuses,
        },
        on: {
          'change-show-all-problems': (shouldShowAll: boolean) => {
            showAllProblems = shouldShowAll;
            showProblems(shouldShowAll);
          },
          'change-visibility': (
            selectedProblems: types.ProblemListItem[],
            visibility: number,
          ) => {
            Promise.all(
              selectedProblems.map((problem: types.ProblemListItem) =>
                api.Problem.update({
                  problem_alias: problem.alias,
                  visibility: visibilityTrue(visibility, problem.visibility),
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
      problems: <types.ProblemListItem[]>[],
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

  function visibilityTrue(
    newVisibility: number,
    oldVisibility: number,
  ): number {
    let visibility: number = 0;
    if (newVisibility == 1) {
      switch (oldVisibility) {
        case payload.visibilityStatuses['privateBanned']: {
          visibility = payload.visibilityStatuses['publicBanned'];
          break;
        }
        case payload.visibilityStatuses['privateWarning']: {
          visibility = payload.visibilityStatuses['publicBanned'];
          break;
        }
        case payload.visibilityStatuses['private']: {
          visibility = payload.visibilityStatuses['public'];
          break;
        }
        default: {
          visibility = oldVisibility;
          break;
        }
      }
    } else if (newVisibility == 0) {
      switch (oldVisibility) {
        case payload.visibilityStatuses['publicBanned']: {
          visibility = payload.visibilityStatuses['privateBanned'];
          break;
        }
        case payload.visibilityStatuses['publicWarning']: {
          visibility = payload.visibilityStatuses['privateWarning'];
          break;
        }
        case payload.visibilityStatuses['public']: {
          visibility = payload.visibilityStatuses['private'];
          break;
        }
        default: {
          visibility = oldVisibility;
          break;
        }
      }
    }
    return visibility;
  }

  showProblems(showAllProblems, /*pageNumber=*/ 1);
});
