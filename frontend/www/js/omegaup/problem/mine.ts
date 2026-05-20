import Vue from 'vue';
import problem_Mine from '../components/problem/Mine.vue';
import { OmegaUp } from '../omegaup';
import { types } from '../api_types';
import T from '../lang';
import * as api from '../api';
import * as ui from '../ui';

OmegaUp.on('ready', () => {
  const payload = types.payloadParsers.ProblemsMineInfoPayload();
  let showAllProblems = false;
  const problemsMine = new Vue({
    el: '#main-container',
    components: {
      'omegaup-problem-mine': problem_Mine,
    },
    data: () => ({
      problems: [] as types.ProblemListItem[],
      pagerItems: [] as types.PageItem[],
    }),
    render: function (createElement) {
      return createElement('omegaup-problem-mine', {
        props: {
          problems: this.problems,
          privateProblemsAlert: payload.privateProblemsAlert,
          isSysadmin: payload.isSysadmin,
          pagerItems: this.pagerItems,
          visibilityStatuses: payload.visibilityStatuses,
          query: payload.query,
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
                  visibility: normalizeVisibility(
                    visibility,
                    problem.visibility,
                  ),
                  message:
                    visibility === 1
                      ? 'private -> public'
                      : 'public -> private',
                }),
              ),
            )
              .then(() => {
                ui.success(T.updateItemsSuccess);
              })
              .catch((error) => {
                ui.error(ui.formatString(T.bulkOperationError, error));
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
          remove: ({
            alias,
            shouldShowAllProblems,
          }: {
            alias: string;
            shouldShowAllProblems: boolean;
          }) => {
            api.Problem.delete({ problem_alias: alias })
              .then(() => {
                ui.success(T.problemSuccessfullyRemoved);
                showAllProblems = shouldShowAllProblems;
                showProblems(shouldShowAllProblems);
              })
              .catch(ui.apiError);
          },
          'remove-all-problems': ({
            selectedProblems,
            shouldShowAllProblems,
          }: {
            selectedProblems: types.ProblemListItem[];
            shouldShowAllProblems: boolean;
          }) => {
            Promise.all(
              selectedProblems.map((problem: types.ProblemListItem) =>
                api.Problem.delete({ problem_alias: problem.alias }),
              ),
            )
              .then(() => {
                ui.success(T.problemSuccessfullyRemoved);
              })
              .catch((error) => {
                ui.error(ui.formatString(T.bulkOperationError, error));
              })
              .finally(() => {
                showAllProblems = shouldShowAllProblems;
                showProblems(shouldShowAllProblems);
              });
          },
        },
      });
    },
  });

  function showProblems(showAllProblems: boolean, pageNumber?: number): void {
    (showAllProblems
      ? api.Problem.adminList({
          page: pageNumber,
          query: payload.query ?? null,
        })
      : api.Problem.myList({
          page: pageNumber,
          query: payload.query ?? null,
        })
    )
      .then((result) => {
        problemsMine.pagerItems = result.pagerItems;
        problemsMine.problems = result.problems;
      })
      .catch(ui.apiError);
  }

  function normalizeVisibility(
    newVisibility: number,
    oldVisibility: number,
  ): number {
    if (newVisibility == 1) {
      switch (oldVisibility) {
        case payload.visibilityStatuses['privateBanned']:
          return payload.visibilityStatuses['publicBanned'];
        case payload.visibilityStatuses['privateWarning']:
          return payload.visibilityStatuses['publicWarning'];
        case payload.visibilityStatuses['private']:
          return payload.visibilityStatuses['public'];
        default:
          return oldVisibility;
      }
    } else if (newVisibility == 0) {
      switch (oldVisibility) {
        case payload.visibilityStatuses['publicBanned']:
          return payload.visibilityStatuses['privateBanned'];
        case payload.visibilityStatuses['publicWarning']:
          return payload.visibilityStatuses['privateWarning'];
        case payload.visibilityStatuses['public']:
          return payload.visibilityStatuses['private'];
        default:
          return oldVisibility;
      }
    }
    return oldVisibility;
  }

  showProblems(showAllProblems, /*pageNumber=*/ 1);
});
