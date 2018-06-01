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
      }
    }
  });
});
