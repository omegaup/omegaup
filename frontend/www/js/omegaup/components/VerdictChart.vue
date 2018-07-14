<template>
  <div></div>
</template>

<script>
export default {
  data: function() {
    return { runCountsChart: null, }
  },
  props: {
    stats: Object,
    contestAlias: String,
  },
  mounted: function() {
    this.runCountsChart =
        oGraph.verdictCounts(this.$el, this.contestAlias, this.stats);
  },
  watch: {
    stats: function() {
      this.runCountsChart.series[0].setData(
          oGraph.normalizeRunCounts(this.stats));
    },
  },
};
</script>
