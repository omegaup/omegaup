<template>
  <!-- id-lint off -->
  <!-- <div id="monthly-solved-problems-chart"></div> -->
  <highcharts v-bind:options="options"></highcharts>
  <!-- id-lint on -->
</template>

<script lang="ts">
import { Vue, Component, Prop, Watch } from 'vue-property-decorator';
import { omegaup } from '../../omegaup';
import T from '../../lang';
import * as UI from '../../ui';
import { types } from '../../api_types';
import { Chart } from 'highcharts-vue';

@Component({
  components: {
    highcharts: Chart,
  },
})
export default class SchoolChart extends Vue {
  @Prop() data!: types.SchoolProblemsSolved[];
  @Prop() school!: string;

  T = T;
  UI = UI;

  get options(): any {
    const solvedProblemsCountData = this.data.map(
      solvedProblemsCount => solvedProblemsCount.problems_solved,
    );
    const solvedProblemsCountCategories = this.data.map(
      solvedProblemsCount =>
        `${solvedProblemsCount.year}-${solvedProblemsCount.month}`,
    );
    return {
      chart: {
        type: 'line',
      },
      title: {
        text: this.UI.formatString(
          this.T.profileSchoolMonthlySolvedProblemsCount, {
              school: this.school,
          }
        ),
      },
      yAxis: {
        min: 0,
        title: {
          text: this.T.profileSolvedProblems,
        },
      },
      xAxis: {
        categories: solvedProblemsCountCategories,
        title: {
          text: this.T.wordsMonths,
        },
        labels: {
          rotation: -45,
        },
      },
      legend: {
        enabled: false,
      },
      tooltip: {
        headerFormat: '',
        pointFormat: '<b>{point.y}<b/>',
      },
      series: [
        {
          data: solvedProblemsCountData,
        },
      ],
    }
  };
}

//   mounted: function() {
//     this.chart = Highcharts.chart('monthly-solved-problems-chart', {
//       chart: { type: 'line' },
      // yAxis: {
      //   min: 0,
      //   title: {
      //     text: this.T.profileSolvedProblems,
      //   },
      // },
      // xAxis: {
      //   title: {
      //     text: T.wordsMonths,
      //   },
      //   labels: {
      //     rotation: -45,
      //   },
      // },
      // legend: {
      //   enabled: false,
      // },
      // tooltip: {
      //   headerFormat: '',
      //   pointFormat: '<b>{point.y}<b/>',
      // },
      // series: [
      //   {
      //     data: [],
      //   },
      // ],
//     });
//     this.renderData();
//   },
//   methods: {
//     renderData: function() {
      // const solvedProblemsCountData = this.data.map(
      //   solvedProblemsCount => solvedProblemsCount.problems_solved,
      // );
      // const solvedProblemsCountCategories = this.data.map(
      //   solvedProblemsCount =>
      //     `${solvedProblemsCount.year}-${solvedProblemsCount.month}`,
      // );

//       this.chart.update(
//         {
//           xAxis: {
//             categories: solvedProblemsCountCategories,
//           },
//           series: [
//             {
//               data: solvedProblemsCountData,
//             },
//           ],
//         },
//         true /* redraw */,
//       );
//     },
//   },
//   watch: {
//     data: function() {
//       this.renderData();
//     },
//   },
// };
</script>
