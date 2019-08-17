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
export default class VerdictChart extends Vue {
  @Prop() stats!: Object;
  @Prop() title!: string;

  runCountsChart = oGraph.verdictCounts(this.$el, this.title, this.stats);

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
