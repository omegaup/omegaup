import contest_ContestList from '../components/contest/ContestList.vue';
import {API, OmegaUp, UI, T} from '../omegaup.js';
import Vue from 'vue';

OmegaUp.on('ready', function() {
  function fillContestsTable() {
    var deferred =
        contestList.showAdmin ? API.Contest.adminList() : API.Contest.myList();
    deferred.then(function(result) { contestList.contests = result.contests; })
        .fail(UI.apiError);
  }

  let payloadElement = document.getElementById('payload');
  let payload = {'contests': []};
  if (payloadElement) {
    payload = JSON.parse(payloadElement.innerText);
    for (var idx in payload.contests) {
      var contest = payload.contests[idx];
      OmegaUp.convertTimes(contest);
    }
  } else {
    fillContestsTable();
  }

  let contestList = new Vue({
    el: '#contest_list',
    render: function(createElement) {
      return createElement('omegaup-contest-contestlist', {
        props: {contests: this.contests, isAdmin: true, title: T.wordsContests},
        on: {
          'toggle-show-admin': showAdmin => {
            this.showAdmin = showAdmin;
            fillContestsTable();
          },
          'bulk-update':
              (admissionMode) => this.changeAdmissionMode(admissionMode),
          'download-csv-users':
              (contestAlias) => this.downloadCsvUsers(contestAlias),
        }
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
        UI.bulkOperation(function(alias, resolve, reject) {
          API.Contest
              .update({contest_alias: alias, 'admission_mode': admissionMode})
              .then(resolve)
              .fail(reject);
        }, fillContestsTable);
      },
      downloadCsvUsers: function(contestAlias) {
        API.Contest.contestants({
                     contest_alias: contestAlias,
                   })
            .then(function(result) {
              if (result.status != 'ok') {
                return;
              }
              // Solution found in
              // https://stackoverflow.com/questions/14964035/how-to-export-javascript-array-info-to-csv-on-client-side
              let csvContent = "data:text/csv;charset=utf-8,";
              result.contestants.forEach(function(rowArray, index) {
                let row = '';
                if (index == 0) {
                  // Setting table headers
                  for (let[key, value] of Object.entries(rowArray)) {
                    if (rowArray.hasOwnProperty(key)) {
                      row += key + ',';
                    }
                  }
                  csvContent += row + "\r\n";
                  row = '';
                }
                for (let[key, value] of Object.entries(rowArray)) {
                  if (rowArray.hasOwnProperty(key)) {
                    row += value + ',';
                  }
                }
                csvContent += row + "\r\n";
              });

              var encodedUri = encodeURI(csvContent);
              var link = document.createElement('a');
              link.setAttribute('href', encodedUri);
              link.setAttribute('download', 'users_' + contestAlias + '.csv');
              document.body.appendChild(link);  // Required for FF

              link.click();  // This will download the data file named
                             // "my_data.csv".
            })
            .fail(omegaup.UI.apiError);
      }
    }
  });
});
