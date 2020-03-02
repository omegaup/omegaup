import contest_Report from '../components/contest/Report.vue';
import { OmegaUp } from '../omegaup.js';
import Vue from 'vue';

OmegaUp.on('ready', function() {
  const contestAlias = /\/contest\/([^\/]+)\/report\/?.*/.exec(
    window.location.pathname,
  )[1];
  const payload = JSON.parse(document.getElementById('payload').innerText);

  let contestReport = new Vue({
    el: '#contest-report',
    render: function(createElement) {
      return createElement('contestReport', {
        props: {
          contestReport: this.contestReport,
          contestAlias: this.contestAlias,
        },
      });
    },
    data: {
      contestReport: payload.contestReport,
      contestAlias: contestAlias,
    },
    components: {
      contestReport: contest_Report,
    },
  });
});
