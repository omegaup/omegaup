<template>
  <!-- id-lint off -->
  <div id="monthly-solved-problems-chart"></div>
  <!-- id-lint on -->
</template>

<script>
import { T } from '../../omegaup.js';
import UI from '../../ui.js';

export default {
  props: {
    data: Array,
    school: String,
  },
  data: function() {
    return {
      T: T,
      UI: UI,
    }
  },
  mounted: function() {
    this.chart = Highcharts.chart('monthly-solved-problems-chart', {
      title: {
        text: this.UI.formatString(this.T.profileSchoolMonthlySolvedProblemsCount, {
          school: this.school
        }),
      },
      chart: { type: 'area' },
      yAxis: {
        min: 0,
        title: {
          text: 'Count'
        },
      },
      legend: {
        enabled: false,
      },
      tooltip: {
        headerFormat: '{point.key}: <b>{point.y}<b/>',
        // pointFormat: '{point.key}: <b>{point.y}<b/>',
      },
    });
    this.renderData();
  },
  methods: {
    renderData: function() {
      let self = this;

      const solvedProblemsCountData = this.data.map(
        solvedProblemsCount => solvedProblemsCount.count,
      );
      const solvedProblemsCountCategories = this.data.map(
        solvedProblemsCount =>
          `${solvedProblemsCount.year}-${solvedProblemsCount.month}`,
      );

      console.log('solvedData: ', solvedProblemsCountData);
      console.log('solvedLabels: ', solvedProblemsCountCategories);
      console.log('SERIES: ', self.chart.series);

      self.chart.update({
        xAxis: {
          categories: solvedProblemsCountCategories,
          title: {
            text: 'Months'
          },
          labels: {
            rotation: -45,
          },
        },
        series: [],
      });
      // Removing old series
      while (self.chart.series.length) self.chart.series[0].remove(false);
      // Adding new series
      self.chart.addSeries({
        data: solvedProblemsCountData,
      });
      self.chart.redraw();
    },
  },
  watch: {
    data: function(newVal) {
      console.log('CAMBIÓ', newVal);
      this.renderData();
    }
  }
}
</script>