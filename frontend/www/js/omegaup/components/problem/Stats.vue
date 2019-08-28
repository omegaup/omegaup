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

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import { T } from '../../omegaup.js';
import UI from '../../ui.js';
import omegaup from '../../api.js';
import verdict_chart from '../VerdictChart.vue';
import distribution_chart from '../DistributionChart.vue';

@Component({
  components: {
    'omegaup-verdict-chart': verdict_chart,
    'omegaup-distribution-chart': distribution_chart,
  },
})
export default class Stats extends Vue {
  @Prop() stats!: omegaup.Stats;
  @Prop() problemAlias!: string;

  T = T;

  get totalRuns(): string {
    return UI.formatString(T.totalRuns, { numRuns: this.stats.total_runs });
  }
}

</script>
