<template>
  <div></div>
</template>

<script>

export default {
	data: function(){
		return{
			distributionChart: null,
		}
	},
  props: {
    stats: Object,
    contestAlias: String,
  },
  mounted: function() { this.drawDistributionChart();},
  watch: {stats: function() { this.updateRunCountsData();}},
  methods: {
    drawDistributionChart: function() {
      // Draw distribution of scores chart
      this.distributionChart =
          oGraph.distributionChart(this.$el, this.contestAlias, this.stats);
    },
    updateRunCountsData: function() {
      this.distributionChart.series[0].setData(
          oGraph.getDistribution(this.stats));
    },
  },
};
</script>
