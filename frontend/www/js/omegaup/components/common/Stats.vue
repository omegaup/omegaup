<template>
  <div class="post">
    <div class="copy">
      <h1>{{ T.liveStatistics }}</h1>
      <div class="total-runs">
        {{ totalRuns }}
      </div>
      <highcharts :options="verdictChartOptions"></highcharts>
      <highcharts :options="distributionChartOptions"></highcharts>
      <highcharts :options="pendingChartOptions"></highcharts>
    </div>
  </div>
</template>

<script lang="ts">
import Vue from 'vue';
import { Component, Prop, Watch } from 'vue-facing-decorator';
import { omegaup } from '../../omegaup';
import T from '../../lang';
import * as ui from '../../ui';
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
    return ui.formatString(T.totalRuns, { numRuns: this.stats.total_runs });
  }

  @Watch('stats')
  onPropertyChanged(newValue: omegaup.Stats): void {
    this.$emit('update-series', newValue);
  }
}
</script>
