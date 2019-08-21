<template>
  <div></div>
</template>

<script lang="ts">
import { Vue, Component, Prop, Emit, Watch } from 'vue-property-decorator';
import { Highcharts } from '@/third_party/js/highstock.js';
import { oGraph } from '../../omegaup-graph.js';
import { T } from '../omegaup.js';
import UI from '../ui.js';
import omegaup from '../api.js';

@Component({})
export default class DistributionChart extends Vue {
  @Prop() stats!: omegaup.Stats;
  @Prop() title!: string;

  distributionChart: omegaup.Stats = Highcharts.Charts;

  mounted() {
    this.distributionChart = oGraph.distributionChart(
      this.$el,
      this.title,
      this.stats,
    );
  }

  @Watch('stats')
  onPropertyChanged(newValue: string): void {
    this.distributionChart.series[0].setData(oGraph.getDistribution(newValue));
  }
}

</script>
