<template>
  <div></div>
</template>

<script>
export default {
  props: {
    stats: Object,
    contestAlias: String,
  },
  mounted: function() { this.draw_pie_chart()},
  methods: {
    draw_pie_chart: function() {
      if (this.stats != null) {
        if (window.run_counts_chart != null) {
          return;
        }

        // Draw verdict counts pie chart
        window.run_counts_chart =
            oGraph.verdictCounts(this.$el, this.contestAlias, this.stats);
      }
      setTimeout(this.draw_pie_chart, 1000);
    }
  },
};
</script>
