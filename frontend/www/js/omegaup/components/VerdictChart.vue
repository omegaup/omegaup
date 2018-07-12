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
    if (this.runCountsChart) return;

    // Draw verdict counts pie chart
    this.runCountsChart =
        oGraph.verdictCounts(this.$el, this.contestAlias, this.stats);
  },

  watch: {stats: function() { this.updateRunCountsData();}},
  methods: {
    updateRunCountsData: function() {
      this.runCountsChart.series[0].setData(
          oGraph.normalizeRunCounts(this.stats));
    },
  },
};
</script>
