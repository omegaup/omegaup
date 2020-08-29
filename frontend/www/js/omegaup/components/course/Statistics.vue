<template>
  <div class="container-fluid">
    <h2 class="text-center">
      <a v-bind:href="`/course/${course.alias}/`">{{ course.name }}</a>
    </h2>
    <br />
    <div>
      <div class="d-flex justify-content-center">
        <select v-model="selected" class="text-center">
          <option v-for="option in options" v-bind:value="option.value">{{
            option.text
          }}</option>
        </select>
      </div>
      <highcharts v-bind:options="selected"></highcharts>
    </div>
  </div>
  <!-- panel -->
</template>

<script lang="ts">
import { Chart } from 'highcharts-vue';
import { Vue, Component, Prop, Watch } from 'vue-property-decorator';
import { types } from '../../api_types';
import T from '../../lang';
import * as ui from '../../ui';
@Component({
  components: {
    highcharts: Chart,
  },
})
export default class Statistics extends Vue {
  @Prop() course!: types.CourseDetails;
  @Prop() problemStats!: types.CourseProblemStatistics[];
  T = T;
  //chart options
  selected = this.varianceChartOptions;
  options = [
    { value: this.varianceChartOptions, text: T.courseStatisticsVariance },
    { value: this.averageChartOptions, text: T.courseStatisticsAverageScore },
    {
      value: this.highScoreChartOptions,
      text: T.courseStatisticsStudentsAbove,
    },
    {
      value: this.lowScoreChartOptions,
      text: T.courseStatisticsStudentsScored,
    },
    { value: this.minimumChartOptions, text: T.courseStatisticsMinimumScore },
    { value: this.maximumChartOptions, text: T.courseStatisticsMaximumScore },
    { value: this.runsChartOptions, text: T.courseStatisticsAverageRuns },
  ];
  //get chart options
  get varianceChartOptions() {
    return this.createChartOptions(
      T.courseStatisticsScoreVariance,
      '{y}',
      T.courseStatisticsVariance,
      this.getStatistic('variance'),
      this.getMaxStat('variance'),
      this.problems,
    );
  }
  get averageChartOptions() {
    return this.createChartOptions(
      T.courseStatisticsAverageScore,
      '{y}',
      T.wordsScore,
      this.getStatistic('average'),
      this.maxPoints,
      this.problems,
    );
  }
  get highScoreChartOptions() {
    return this.createChartOptions(
      T.courseStatisticsStudentsAbove,
      '{y} %',
      T.wordsPercentage,
      this.getStatistic('high_score_percentage'),
      100,
      this.problems,
    );
  }
  get lowScoreChartOptions() {
    return this.createChartOptions(
      T.courseStatisticsStudentsScored,
      '{y} %',
      T.wordsPercentage,
      this.getStatistic('low_score_percentage'),
      100,
      this.problems,
    );
  }
  get minimumChartOptions() {
    return this.createChartOptions(
      T.courseStatisticsMinimumScore,
      '{y}',
      T.wordsScore,
      this.getStatistic('minimum'),
      this.maxPoints,
      this.problems,
    );
  }
  get maximumChartOptions() {
    return this.createChartOptions(
      T.courseStatisticsMaximumScore,
      '{y}',
      T.wordsScore,
      this.getStatistic('maximum'),
      this.maxPoints,
      this.problems,
    );
  }
  get runsChartOptions() {
    return this.createChartOptions(
      T.courseStatisticsAverageRuns,
      '{y}',
      T.wordsRuns,
      this.getStatistic('avg_runs'),
      this.getMaxStat('avg_runs'),
      this.problems,
    );
  }
  //helper functions
  get problems() {
    return this.problemStats.map(
      (problem) => `${problem.assignment_alias} - ${problem.problem_alias}`,
    );
  }
  get maxPoints() {
    let maxPoints = 0;
    for (const problem of this.problemStats) {
      if (problem.max_points > maxPoints) maxPoints = problem.max_points;
    }
    return maxPoints;
  }
  getMaxStat(statistic: 'variance' | 'avg_runs') {
    let max = 0;
    for (const stat of this.getStatistic(statistic)) {
      if (stat > max) max = stat;
    }
    return max;
  }
  getStatistic(
    name:
      | 'variance'
      | 'average'
      | 'avg_runs'
      | 'high_score_percentage'
      | 'low_score_percentage'
      | 'maximum'
      | 'minimum',
  ) {
    return this.problemStats.map((problem) => Math.round(problem[name] || 0));
  }
  //yLabel = '{y}' or '{y} %'
  //yName = data type (percentage, score, etc.)
  //data = getStatistics("chart_type")
  //yMax = get maxPoints() or getMaxStat
  createChartOptions(
    title: string,
    yLabel: string,
    yName: string,
    data: number[],
    yMax: number,
    problems: string[],
  ) {
    return {
      chart: {
        type: 'bar',
      },
      title: {
        text: title,
      },
      xAxis: {
        categories: problems,
        title: T.wordsProblem,
        min: 0,
      },
      yAxis: {
        min: 0,
        max: yMax,
        title: yName,
      },
      tooltip: {},
      plotOptions: {
        bar: {
          dataLabels: {
            enabled: true,
            format: yLabel,
          },
        },
      },
      series: [
        {
          name: yName,
          data: data,
        },
      ],
    };
  }
}
</script>
