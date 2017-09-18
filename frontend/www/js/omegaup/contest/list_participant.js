import contest_List from '../components/contest/ContestList.vue';
import {OmegaUp} from '../omegaup.js';
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
  let issetIsIndex = document.getElementById('is-index');
  let isIndex = issetIsIndex ? JSON.parse(issetIsIndex.innerText) : false;

  let contestMyList = new Vue({
    el: '#my-next-contests',
    render: function(createElement) {
      return createElement('my-next-contests', {
        props: {
          contests: this.contests,
          isParticipant: this.isParticipant,
          isIndex: this.isIndex
        },
      });
    },
    data: {contests: payload.contests, isParticipant: true, isIndex: isIndex},
    components: {
      'my-next-contests': contest_List,
    },
  });
});
