<template>
  <div></div>
</template>

<script>
import {bus} from '../contest/stats';
export default {
  props: {
    stats: Object,
    contestAlias: String,
  },
  mounted: function() { this.draw_pie_chart();},
  watch: {
    stats: function() {
      this.draw_pie_chart();
      this.updateRunCountsData();
      this.runCountsChart();
    }
  },
  methods: {
    draw_pie_chart: function() {
      if (this.stats != null) {
        if (this.$el.run_counts_chart != null) {
          return;
        }

        // Draw verdict counts pie chart
        this.$el.run_counts_chart =
            oGraph.verdictCounts(this.$el, this.contestAlias, this.stats);
      }
    },
    updateRunCountsData: function() {
      this.$el.run_counts_chart.series[0].setData(
          oGraph.normalizeRunCounts(this.stats));
    },
    runCountsChart: function() { bus.$emit('runCountsChart', true);}
  },
};
</script>
