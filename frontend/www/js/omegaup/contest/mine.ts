import Vue from 'vue';
import { OmegaUp } from '../omegaup';
import { types } from '../api_types';
import T from '../lang';
import * as api from '../api';
import * as ui from '../ui';
import * as CSV from '@/third_party/js/csv.js/csv.js';
import contest_Mine from '../components/contest/Mine.vue';

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
          'show-archived-contests': (shouldShowArchivedContests: boolean) => {
            showArchivedContests = shouldShowArchivedContests;
            fillContestsTable(showAllContests, showArchivedContests);
          },
          'change-show-all-contests': (shouldShowAll: boolean) => {
            showAllContests = shouldShowAll;
            fillContestsTable(showAllContests, showArchivedContests);
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
                fillContestsTable(showAllContests, showArchivedContests);
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
                const dataToSerialize = {
                  fields: [
                    { id: 'name' },
                    { id: 'username' },
                    { id: 'email' },
                    { id: 'state' },
                    { id: 'country' },
                    { id: 'school' },
                  ],
                  records: result.contestants,
                };
                const dialect = {
                  dialect: {
                    csvddfVersion: 1.2,
                    delimiter: ',',
                    doubleQuote: true,
                    lineTerminator: '\r\n',
                    quoteChar: '"',
                    skipInitialSpace: true,
                    header: true,
                    commentChar: '#',
                  },
                };
                const csvContent =
                  'data:text/csv;charset=utf-8,' +
                  CSV.serialize(dataToSerialize, dialect);
                const encodedUri = encodeURI(csvContent);
                const link = document.createElement('a');
                link.setAttribute('href', encodedUri);
                link.setAttribute('download', `users_${contestAlias}.csv`);
                document.body.appendChild(link); // Required for FF

                link.click(); // This will download the data
              })
              .catch(ui.apiError);
          },
        },
      });
    },
  });

  function fillContestsTable(
    showAllContests: boolean,
    showArchivedContests: boolean,
  ): void {
    const param = { show_archived: showArchivedContests };
    (showAllContests ? api.Contest.adminList(param) : api.Contest.myList(param))
      .then((result) => {
        contestMine.contests = result.contests;
      })
      .catch(ui.apiError);
  }
});
