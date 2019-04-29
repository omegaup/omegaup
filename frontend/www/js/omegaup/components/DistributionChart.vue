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
    title: String,
  },
  mounted: function() {
    this.distributionChart =
        oGraph.distributionChart(this.$el, this.title, this.stats);
  },
  watch: {
    stats: function() {
      this.distributionChart.series[0].setData(
          oGraph.getDistribution(this.stats));
    },
  },
};
</script>
