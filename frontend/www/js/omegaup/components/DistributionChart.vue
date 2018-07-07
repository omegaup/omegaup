<template>
  <div></div>
</template>

<script>

export default {
  props: {
    stats: Object,
    contestAlias: String,
  },
  mounted: function() { this.draw_distribution_chart();},
  watch: {stats: function() { this.updateRunCountsData();}},
  methods: {
    draw_distribution_chart: function() {
      // Draw distribution of scores chart
      this.$el.distribution_chart =
          oGraph.distributionChart(this.$el, this.contestAlias, this.stats);
    },
    updateRunCountsData: function() {
      this.$el.distribution_chart.series[0].setData(
          oGraph.getDistribution(this.stats));
    },
  },
};
</script>
