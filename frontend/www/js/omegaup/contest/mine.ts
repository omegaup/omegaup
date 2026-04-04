import Vue from 'vue';
import { OmegaUp } from '../omegaup';
import { types } from '../api_types';
import T from '../lang';
import * as api from '../api';
import * as ui from '../ui';
import contest_Mine from '../components/contest/Mine.vue';
import { downloadCsvFile } from '../groups';

OmegaUp.on('ready', () => {
  const payload = types.payloadParsers.ContestListMinePayload();
  let showAllContests = false;
  let showArchivedContests = false;
  const contestMine = new Vue({
    el: '#main-container',
    components: {
      'omegaup-contest-mine': contest_Mine,
    },
    data: () => ({
      contests: payload.contests,
    }),
    render: function (createElement) {
      return createElement('omegaup-contest-mine', {
        props: {
          contests: this.contests,
          privateContestsAlert: payload.privateContestsAlert,
        },
        on: {
          'change-show-archived-contests': (
            shouldShowArchivedContests: boolean,
          ) => {
            showArchivedContests = shouldShowArchivedContests;
            fillContestsTable({ showAllContests, showArchivedContests });
          },
          'change-show-all-contests': (shouldShowAll: boolean) => {
            showAllContests = shouldShowAll;
            fillContestsTable({ showAllContests, showArchivedContests });
          },
          'change-admission-mode': (
            selectedContests: string[],
            visibility: number,
          ) => {
            Promise.all(
              selectedContests.map((contestAlias: string) =>
                api.Contest.update({
                  contest_alias: contestAlias,
                  admission_mode: visibility,
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
                fillContestsTable({ showAllContests, showArchivedContests });
              });
          },
          'download-csv-users': (contestAlias: string) => {
            api.Contest.contestants({
              contest_alias: contestAlias,
            })
              .then((result) => {
                if (!result.contestants) {
                  return;
                }
                downloadCsvFile({
                  fileName: `users_${contestAlias}.csv`,
                  columns: [
                    'name',
                    'username',
                    'email',
                    'state',
                    'country',
                    'school',
                  ],
                  records: result.contestants,
                });
              })
              .catch(ui.apiError);
          },
        },
      });
    },
  });

  function fillContestsTable({
    showAllContests,
    showArchivedContests,
  }: {
    showAllContests: boolean;
    showArchivedContests: boolean;
  }): void {
    const param = { show_archived: showArchivedContests };
    (showAllContests ? api.Contest.adminList(param) : api.Contest.myList(param))
      .then((result) => {
        contestMine.contests = result.contests;
      })
      .catch(ui.apiError);
  }
});
