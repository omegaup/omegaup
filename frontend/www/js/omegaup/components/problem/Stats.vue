<template>
  <div class="post">
    <div class="copy">
      <h1>{{ T.liveStatistics }}</h1>
      <div>
        {{ totalRuns }}
      </div><omegaup-verdict-chart v-bind:stats="stats"
           v-bind:title="problemAlias"></omegaup-verdict-chart>
           <omegaup-distribution-chart v-bind:stats="stats"
           v-bind:title="problemAlias"></omegaup-distribution-chart>
      <div class="pending-runs-chart"></div>
    </div>
  </div>
</template>

<script>
import {T} from '../../omegaup.js';
import verdict_chart from '../VerdictChart.vue';
import distribution_chart from '../DistributionChart.vue';

export default {
  props: {
    stats: Object,
    problemAlias: String,
  },
  computed: {
    totalRuns: function() {
      return omegaup.UI.formatString(omegaup.T.totalRuns,
                                     {numRuns: this.stats.total_runs});
    }
  },
  components: {
    'omegaup-verdict-chart': verdict_chart,
    'omegaup-distribution-chart': distribution_chart,
  },
  data: function() {
    return {
      T: T,
    };
  },
};
</script>
