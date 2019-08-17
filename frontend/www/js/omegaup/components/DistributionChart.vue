<template>
  <div></div>
</template>

<script lang="ts">
import { Vue, Component, Prop, Emit, Watch } from 'vue-property-decorator';
import { T } from '../omegaup.js';
import UI from '../ui.js';
import omegaup from '../api.js';
import oGraph from '../../omegaup-graph.js';

@Component({})
export default class DistributionChart extends Vue {
  @Prop() stats!: Object;
  @Prop() title!: string;

  distributionChart = oGraph.distributionChart(
    this.$el,
    this.title,
    this.stats,
  );

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
