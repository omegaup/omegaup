<template>
  <div class="post">
    <div class="copy">
      <h1>{{ T.liveStatistics }}</h1>
      <div class="total-runs">
        {{ totalRuns }}
      </div>
      <highcharts v-bind:options="verdictChartOptions"></highcharts>
      <highcharts v-bind:options="distributionChartOptions"></highcharts>
      <highcharts v-bind:options="pendingChartOptions"></highcharts>
    </div>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop, Watch } from 'vue-property-decorator';
import { omegaup } from '../../omegaup';
import T from '../../lang';
import * as UI from '../../ui';
import { Chart } from 'highcharts-vue';

@Component({
  components: {
    highcharts: Chart,
  },
})
export default class Stats extends Vue {
  @Prop() stats!: omegaup.Stats;
  @Prop() verdictChartOptions!: Chart;
  @Prop() distributionChartOptions!: Chart;
  @Prop() pendingChartOptions!: Chart;

  T = T;

  get totalRuns(): string {
    return UI.formatString(T.totalRuns, { numRuns: this.stats.total_runs });
  }

  @Watch('stats')
  onPropertyChanged(newValue: omegaup.Stats): void {
    this.$emit('update-series', newValue);
  }
}
</script>
