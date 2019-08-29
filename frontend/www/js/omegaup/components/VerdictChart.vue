<template>
  <div></div>
</template>

<script lang="ts">
import { Vue, Component, Prop, Emit, Watch } from 'vue-property-decorator';
import * as Highcharts from 'highcharts';
import { oGraph } from '../../omegaup-graph.js';
import { T } from '../omegaup.js';
import UI from '../ui.js';
import omegaup from '../api.js';

@Component({})
export default class VerdictChart extends Vue {
  @Prop() stats!: omegaup.Stats;
  @Prop() title!: string;

  runCountsChart: Highcharts.Chart = new Highcharts.Chart(
    this.$el as HTMLElement,
    {},
  );

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
