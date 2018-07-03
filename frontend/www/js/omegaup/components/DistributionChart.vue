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
  data: function() {
    return { verdictChartRunCountsChart: false, }
  },
  mounted: function() { this.draw_distribution_chart();},
  watch: {
    stats: function() {
      this.draw_distribution_chart();
      this.updateRunCountsData();
      this.distributionChart();
    }
  },
  methods: {
    draw_distribution_chart: function() {
      if (this.stats != null) {
        if (this.verdictChartRunCountsChart == true) {
          return;
        }
        // Draw distribution of scores chart
        this.$el.distribution_chart =
            oGraph.distributionChart(this.$el, this.contestAlias, this.stats);
      }
    },
    updateRunCountsData: function() {
      this.$el.distribution_chart.series[0].setData(
          oGraph.getDistribution(this.stats));
    },
    distributionChart: function() {
      bus.$on('runCountsChart',
              (data) => { this.verdictChartRunCountsChart = data; })
    }
  },
};
</script>
