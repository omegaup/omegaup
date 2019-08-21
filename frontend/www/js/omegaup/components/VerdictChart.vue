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
export default class VerdictChart extends Vue {
  @Prop() stats!: omegaup.Stats;
  @Prop() title!: string;

  runCountsChart: omegaup.Stats = Highcharts.Chart;

  mounted() {
    this.runCountsChart = oGraph.verdictCounts(
      this.$el,
      this.title,
      this.stats,
    );
  }

  @Watch('stats')
  onPropertyChanged(newValue: string): void {
    this.runCountsChart.series[0].setData(oGraph.normalizeRunCounts(newValue));
  }
}

</script>
