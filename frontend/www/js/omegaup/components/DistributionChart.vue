<template>
  <div></div>
</template>

<script>
export default {
  data: function() {
    return {
      distributionChart: null,
    };
  },
  props: {
    stats: Object,
    contestAlias: String,
  },
  mounted: function() {
    this.distributionChart =
        oGraph.distributionChart(this.$el, this.contestAlias, this.stats);
  },
  watch: {
    stats: function() {
      this.distributionChart.series[0].setData(
          oGraph.getDistribution(this.stats));
    },
  },
};
</script>
