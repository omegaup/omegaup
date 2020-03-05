import { API, UI, OmegaUp, T } from '../omegaup.js';
import Vue from 'vue';
import arena_ContestList from '../components/arena/ContestList.vue';

OmegaUp.on('ready', function() {
  Date.setLocale(omegaup.T.locale);
  const payload = JSON.parse(document.getElementById('payload').innerText);
  for (const timeType in payload.contests) {
    payload[timeType] = convertContestTimes(payload.contests[timeType]);
  }
  const contestList = new Vue({
    el: '#arena-contest-list',
    render: function(createElement) {
      return createElement('omegaup-arena-contestlist', {
        props: {
          initialQuery: this.initialQuery,
          contests: this.contests,
          isLogged: this.isLogged,
        },
      });
    },
    data: {
      initialQuery: payload.query,
      isLogged: payload.isLogged,
      contests: payload.contests,
    },
    components: { 'omegaup-arena-contestlist': arena_ContestList },
  });

  function convertContestTimes(contests) {
    return contests.forEach(contest => OmegaUp.convertTimes(contest));
  }
});
