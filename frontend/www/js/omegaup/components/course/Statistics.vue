<template>
  <div class="container-fluid">
    <h2 class="text-center">
      <a :href="`/course/${course.alias}/`">{{ course.name }}</a>
    </h2>
    <br />
    <div>
      <div class="d-flex justify-content-center">
        <select v-model="selected" class="text-center">
          <option v-for="option in options" :value="option.value">
            {{ option.text }}
          </option>
        </select>
      </div>
      <highcharts :options="selected"></highcharts>
    </div>
  </div>
  <!-- panel -->
</template>

<script lang="ts">
import { Chart } from 'highcharts-vue';
import { Vue, Component, Prop } from 'vue-property-decorator';
import { types } from '../../api_types';
import T from '../../lang';

const ORDERED_VERDICTS = [
  'AC',
  'PA',
  'WA',
  'RTE',
  'RFE',
  'CE',
  'PE',
  'TLE',
  'OLE',
  'MLE',
  'JE',
  'VE',
];

@Component({
  components: {
    highcharts: Chart,
  },
})
export default class Statistics extends Vue {
  @Prop() course!: types.CourseDetails;
  @Prop() problemStats!: types.CourseProblemStatistics[];
  @Prop() verdicts!: types.CourseProblemVerdict[];
  T = T;
  // chart options
  selected = this.completedScoreChartOptions;
  options = [
    { value: this.averageChartOptions, text: T.courseStatisticsAverageScore },
    {
      value: this.completedScoreChartOptions,
      text: T.courseStatisticsStudentsCompleted,
    },
    {
      value: this.highScoreChartOptions,
      text: T.courseStatisticsStudentsAbove,
    },
    {
      value: this.lowScoreChartOptions,
      text: T.courseStatisticsStudentsScored,
    },
    { value: this.runsChartOptions, text: T.courseStatisticsAverageRuns },
    { value: this.verdictChartOptions, text: T.courseStatisticsVerdicts },
    { value: this.varianceChartOptions, text: T.courseStatisticsVariance },
    { value: this.minimumChartOptions, text: T.courseStatisticsMinimumScore },
    { value: this.maximumChartOptions, text: T.courseStatisticsMaximumScore },
  ];
  // get chart options
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
  get completedScoreChartOptions() {
    return this.createChartOptions(
      T.courseStatisticsStudentsCompleted,
      '{y} %',
      T.wordsPercentage,
      this.getStatistic('completed_score_percentage'),
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
  get verdictChartOptions() {
    return {
      chart: {
        type: 'bar',
        height:
          this.problems.length < 15 ? null : `${this.problems.length * 3}%`,
      },
      title: {
        text: T.courseStatisticsVerdicts,
      },
      xAxis: {
        categories: this.problems,
        title: T.wordsProblem,
        min: 0,
      },
      yAxis: {
        min: 0,
        max: 100,
        title: T.wordsRuns,
        reversedStacks: false,
      },
      tooltip: {},
      plotOptions: {
        series: {
          stacking: 'normal',
          pointWidth: 15,
        },
        bar: {
          dataLabels: {
            enabled: true,
            format: '{y} %',
          },
        },
      },
      series: this.verdictStats,
    };
  }
  // helper functions
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
  problemIndices() {
    const indices: {
      [assignmentAlias: string]: { [problemAlias: string]: number };
    } = {};
    this.problemStats.forEach((problem, index) => {
      if (
        !Object.prototype.hasOwnProperty.call(indices, problem.assignment_alias)
      )
        indices[problem.assignment_alias] = {};
      indices[problem.assignment_alias][problem.problem_alias] = index;
    });
    return indices;
  }
  get runsPerAssignment() {
    const problems: string[] = this.problems;
    const problemIndices = this.problemIndices();
    let runSum: number[] = new Array(problems.length).fill(0);
    for (const stat of this.verdicts) {
      runSum[problemIndices[stat.assignment_alias][stat.problem_alias]] +=
        stat.runs;
    }
    return runSum;
  }
  verdictList(): string[] {
    let verdicts: string[] = [];
    for (const stat of this.verdicts) {
      if (stat.verdict && !verdicts.includes(stat.verdict))
        verdicts.push(stat.verdict);
    }
    return verdicts;
  }
  get verdictStats() {
    const verdicts: string[] = this.verdictList();
    const assignmentRuns: number[] = this.runsPerAssignment;
    const problemCount: number = this.problems.length;
    let series: { name: string; data: number[] }[] = [];
    const indices = this.problemIndices();
    const verdictIndices: { [verdict: string]: number } = {};
    verdicts.forEach((verdict, index) => {
      verdictIndices[verdict] = index;
    });
    // create zero-d out runs 2D array
    const runs: number[][] = verdicts.map(() =>
      new Array(problemCount).fill(0),
    );
    // fill runs with verdict runs
    for (const stat of this.verdicts) {
      if (!stat.verdict) break;
      runs[verdictIndices[stat.verdict]][
        indices[stat.assignment_alias][stat.problem_alias]
      ] += stat.runs;
    }
    // turn verdict run sums to percent
    for (let i = 0; i < runs.length; i++) {
      for (let j = 0; j < runs[i].length; j++) {
        if (assignmentRuns[j])
          runs[i][j] = parseFloat(
            ((runs[i][j] / assignmentRuns[j]) * 100).toFixed(1),
          );
      }
    }

    for (const verdictName of ORDERED_VERDICTS) {
      if (!verdicts.includes(verdictName)) {
        continue;
      }
      series.push({
        name: verdictName,
        data: runs[verdicts.indexOf(verdictName)],
      });
    }

    return series;
  }
  getStatistic(
    name:
      | 'variance'
      | 'average'
      | 'avg_runs'
      | 'completed_score_percentage'
      | 'high_score_percentage'
      | 'low_score_percentage'
      | 'maximum'
      | 'minimum',
  ) {
    return this.problemStats.map((problem) =>
      parseFloat((problem[name] || 0).toFixed(1)),
    );
  }
  // yLabel = '{y}' or '{y} %'
  // yName = data type (percentage, score, etc.)
  // data = getStatistics("chart_type")
  // yMax = get maxPoints() or getMaxStat
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
        height: data.length < 15 ? null : `${data.length * 3}%`,
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
          pointWidth: 15,
        },
      ],
    };
  }
}
</script>
