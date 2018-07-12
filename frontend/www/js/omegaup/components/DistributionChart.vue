<template>
  <div></div>
</template>

<script>

export default {
  data: function() {
    return { distributionChart: null, }
  },
  props: {
    stats: Object,
    contestAlias: String,
  },
  mounted: function() {
    if (this.distributionChart) return;
    // Draw distribution of scores chart
    this.distributionChart =
        oGraph.distributionChart(this.$el, this.contestAlias, this.stats);
  },
  watch: {stats: function() { this.updateRunCountsData();}},
  methods: {
    updateRunCountsData: function() {
      this.distributionChart.series[0].setData(
          oGraph.getDistribution(this.stats));
    },
  },
};
</script>
