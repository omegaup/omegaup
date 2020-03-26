import contest_ContestList from '../components/contest/ContestList.vue';
import { API, OmegaUp, UI, T } from '../omegaup.js';
import * as CSV from '../../../third_party/js/csv.js/csv.js';
import Vue from 'vue';

OmegaUp.on('ready', function() {
  function fillContestsTable() {
    (contestList.showAdmin ? API.Contest.adminList() : API.Contest.myList())
      .then(function(result) {
        contestList.contests = result.contests;
      })
      .catch(UI.apiError);
  }

  const payloadElement = document.getElementById('payload');
  let payload = { contests: [] };
  if (payloadElement) {
    payload = JSON.parse(payloadElement.innerText);
    for (let idx in payload.contests) {
      let contest = payload.contests[idx];
      OmegaUp.convertTimes(contest);
    }
  } else {
    fillContestsTable();
  }

  let contestList = new Vue({
    el: '#contest_list',
    render: function(createElement) {
      return createElement('omegaup-contest-contestlist', {
        props: {
          contests: this.contests,
          isAdmin: true,
          title: T.wordsContests,
        },
        on: {
          'toggle-show-admin': showAdmin => {
            this.showAdmin = showAdmin;
            fillContestsTable();
          },
          'bulk-update': admissionMode =>
            this.changeAdmissionMode(admissionMode),
          'download-csv-users': contestAlias =>
            this.downloadCsvUsers(contestAlias),
        },
      });
    },
    data: {
      contests: payload.contests,
      showAdmin: false,
    },
    components: {
      'omegaup-contest-contestlist': contest_ContestList,
    },
    methods: {
      changeAdmissionMode: function(admissionMode) {
        var promises = [];
        $('input[type=checkbox]').each(function() {
          if (this.checked) {
            promises.push(
              API.Contest.update({
                contest_alias: this.id,
                admission_mode: admissionMode,
              }),
            );
          }
        });

        Promise.all(promises)
          .then(() => {
            UI.success(T.updateItemsSuccess);
          })
          .catch(error => {
            UI.error(UI.formatString(T.bulkOperationError, error));
          })
          .finally(() => {
            fillContestsTable();
          });
      },
      downloadCsvUsers: function(contestAlias) {
        API.Contest.contestants({
          contest_alias: contestAlias,
        })
          .then(result => {
            if (result.status != 'ok') {
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

            let encodedUri = encodeURI(csvContent);
            let link = document.createElement('a');
            link.setAttribute('href', encodedUri);
            link.setAttribute('download', `users_${contestAlias}.csv`);
            document.body.appendChild(link); // Required for FF

            link.click(); // This will download the data
          })
          .catch(omegaup.UI.apiError);
      },
    },
  });
});
