<template>
  <div></div>
</template>

<script>
export default {
  props: {
    stats: Object,
    contestAlias: String,
  },
  mounted: function() { 
    this.draw_pie_chart();
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
      setTimeout(this.draw_pie_chart, 1000);
      setTimeout(this.updateRunCountsData, 10000);
    },
    updateRunCountsData: function() {
      this.$el.run_counts_chart.series[0].setData(oGraph.normalizeRunCounts(this.stats));
      setTimeout(this.updateRunCountsData, 10000);
    }
  },
};
</script>
