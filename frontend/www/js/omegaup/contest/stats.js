import contest_Stats from '../components/contest/Stats.vue';
import Vue from 'vue';
import {OmegaUp} from '../omegaup.js';

OmegaUp.on('ready', function() {
  var ChartsDrawn = false;
  var contestAlias =
      /\/contest\/([^\/]+)\/stats\/?.*/.exec(window.location.pathname)[1];

  Highcharts.setOptions({global: {useUTC: false}});
  var callStatsApiTimeout = 10 * 1000;
  var updatePendingRunsChartTimeout = callStatsApiTimeout / 2;
  var pendingChart = null;
  function getStats() {
    omegaup.API.Contest.stats({contest_alias: contestAlias})
        .then(function(s) {
          if (ChartsDrawn != true) {
            ChartsDrawn = true;
            var stats = new Vue({
              el: '#contest-stats',
              render: function(createElement) {
                return createElement('contestStats', {
                  props: {
                    stats: this.stats,
                    contestAlias: this.contestAlias,
                  }
                });
              },
              data: {
                stats: s,
                contestAlias: contestAlias,
              },
              components: {
                'contestStats': contest_Stats,
              },
            });
          
          }
          if(stats){
            // Pending runs chart
           pendingChart = oGraph.pendingRuns(
                updatePendingRunsChartTimeout,
                () => s.pending_runs.length);
           stats.stats = s;
          }
        })
        .fail(omegaup.UI.apiError);
  };

  setInterval(() => getStats(), callStatsApiTimeout);
  getStats();

  
});
