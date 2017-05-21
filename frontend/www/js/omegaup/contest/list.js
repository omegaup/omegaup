import contest_ContestList from '../components/contest/ContestList.vue';
import {API,OmegaUp,UI} from '../omegaup.js';
import Vue from 'vue';

OmegaUp.on('ready', function() {
  function fillContestsTable() {
    var deferred = contestList.showAdmin ?
                       API.Contest.adminList() :
                       API.Contest.myList();
    deferred
        .then(function(result) {
          contestList.contests = result.contests;
        })
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
        props: {contests: this.contests},
        on: {
          'toggle-show-admin': showAdmin => {
            this.showAdmin = showAdmin;
            fillContestsTable();
          },
          'bulk-update': (publiclyVisible) => this.makePublic(publiclyVisible),
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
      makePublic: function (isPublic) {
        UI.bulkOperation(
            function(alias, resolve, reject) {
              API.Contest
                  .update({contest_alias: alias, 'public': isPublic ? 1 : 0})
                  .then(resolve)
                  .fail(reject);
            },
            fillContestsTable);
      }
    }
  });
});
