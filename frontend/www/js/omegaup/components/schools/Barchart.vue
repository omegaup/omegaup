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
    let self = this;
    self.renderData();
  },
  methods: {
    renderData: function() {
      let self = this;

      const solvedProblemsCountData = this.data.map(
        solvedProblemsCount => solvedProblemsCount.count,
      );
      const solvedProblemsCountLabels = this.data.map(
        solvedProblemsCount =>
          `${solvedProblemsCount.year}-${solvedProblemsCount.month}`,
      );

      console.log('solvedData: ', solvedProblemsCountData);
      console.log('solvedLabels: ', solvedProblemsCountLabels);
      console.log('SERIES: ', self.chart.series);
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