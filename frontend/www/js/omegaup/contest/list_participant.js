import contest_List from '../components/contest/ContestList.vue';
import {OmegaUp, T} from '../omegaup.js';
import Vue from 'vue';

OmegaUp.on('ready', function() {
  let payloadElement = document.getElementById('my-next-contests-payload');
  let payload = {'contests': []};
  if (payloadElement) {
    payload = JSON.parse(payloadElement.innerText);
    for (var idx in payload.contests) {
      var contest = payload.contests[idx];
      OmegaUp.convertTimes(contest);
    }
  }
  let contestMyList = new Vue({
    el: '#my-next-contests',
    render: function(createElement) {
      return createElement('my-next-contests', {
        props: {
          contests: this.contests,
          isAdmin: false,
          title: T.contestMyActiveContests
        },
      });
    },
    data: {contests: payload.contests},
    components: {
      'my-next-contests': contest_List,
    },
  });
});
