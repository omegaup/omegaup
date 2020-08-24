import course_Statistics from '../components/course/Statistics.vue';
import { OmegaUp } from '../omegaup';
import { types } from '../api_types';
import * as api from '../api';
import * as ui from '../ui';
import T from '../lang';
import Vue from 'vue';
import { numberFormat } from 'highcharts';

OmegaUp.on('ready', function () {
  const payload = types.payloadParsers.CourseStatisticsPayload();

  const getProblems = (stats: types.CourseStatisticsPayload) => {
    let problems = [];
    for (const problem in stats.problemStats) {
      problems.push(
        `${stats.problemStats[problem].assignment_alias} - ${stats.problemStats[problem].problem_alias}`,
      );
    }
    return problems;
  };

  const getVariance = (stats: types.CourseStatisticsPayload) => {
    let variance = [];
    for (const problem in stats.problemStats) {
      variance.push(stats.problemStats[problem].variance || 0);
    }
    return variance;
  };

  const getAverage = (stats: types.CourseStatisticsPayload) => {
    let average = [];
    for (const problem in stats.problemStats) {
      average.push(stats.problemStats[problem].average || 0);
    }
    return average;
  };

  const gethighScoreCount = (stats: types.CourseStatisticsPayload) => {
    let highScore = [];
    const studentCount = stats.course.student_count;
    if (studentCount) {
      for (const problem in stats.problemStats) {
        highScore.push(
          (stats.problemStats[problem].highScoreCount / studentCount) * 100,
        );
      }
    }
    return highScore || 0;
  };

  const getlowScoreCount = (stats: types.CourseStatisticsPayload) => {
    let lowScore = [];
    const studentCount = stats.course.student_count;
    if (studentCount) {
      for (const problem in stats.problemStats) {
        lowScore.push(
          (stats.problemStats[problem].lowScoreCount / studentCount) * 100,
        );
      }
    }
    return lowScore || 0;
  };

  const getMaximum = (stats: types.CourseStatisticsPayload) => {
    let maximum = [];
    for (const problem in stats.problemStats) {
      maximum.push(stats.problemStats[problem].maximum || 0);
    }
    return maximum;
  };

  const getMinimum = (stats: types.CourseStatisticsPayload) => {
    let minimum = [];
    for (const problem in stats.problemStats) {
      minimum.push(stats.problemStats[problem].minimum || 0);
    }
    return minimum;
  };

  const getMaxPoints = (stats: types.CourseStatisticsPayload) => {
    let maxPoints = 0;
    for (const problem in stats.problemStats) {
      if (stats.problemStats[problem].maxPoints > maxPoints)
        maxPoints = stats.problemStats[problem].maxPoints;
    }
    return maxPoints;
  };

  const getMaxVariance = (stats: types.CourseStatisticsPayload) => {
    let maxVariance = 0;
    const variance = getVariance(stats);
    for (let i = 0; i < variance.length; i++) {
      if (variance[i] > maxVariance) maxVariance = variance[i];
    }
    return maxVariance;
  };

  const viewProgress = new Vue({
    el: '#main-container',
    render: function (createElement) {
      return createElement('omegaup-course-statistics', {
        props: {
          T: T,
          course: payload.course,
          varianceChartOptions: this.varianceChartOptions,
          averageChartOptions: this.averageChartOptions,
          highScoreChartOptions: this.highScoreChartOptions,
          lowScoreChartOptions: this.lowScoreChartOptions,
          maximumChartOptions: this.maximumChartOptions,
          minimumChartOptions: this.minimumChartOptions,
          chartOptions: this.varianceChartOptions,
        },
      });
    },
    data: {
      varianceChartOptions: {
        chart: {
          type: 'bar',
        },
        title: {
          text: T.wordsScoreVariance,
        },
        xAxis: {
          categories: getProblems(payload),
          title: T.wordsProblem,
          min: 0,
        },
        yAxis: {
          min: 0,
          max: getMaxVariance(payload),
          title: T.wordsVariance,
        },
        tooltip: {},
        plotOptions: {
          bar: {
            dataLabels: {
              enabled: true,
              format: '{y}',
            },
          },
        },
        series: [
          {
            name: T.wordsVariance,
            data: getVariance(payload),
          },
        ],
      },
      averageChartOptions: {
        chart: {
          type: 'bar',
        },
        title: {
          text: T.wordsAverageScore,
        },
        xAxis: {
          categories: getProblems(payload),
          title: T.wordsProblem,
          min: 0,
        },
        yAxis: {
          min: 0,
          max: getMaxPoints(payload),
          title: T.wordsScore,
        },
        tooltip: {},
        plotOptions: {
          bar: {
            dataLabels: {
              enabled: true,
              format: '{y}',
            },
          },
        },
        series: [
          {
            name: T.wordsScore,
            data: getAverage(payload),
          },
        ],
      },
      highScoreChartOptions: {
        chart: {
          type: 'bar',
        },
        title: {
          text: `${T.wordsStudentsAbove} 60%`,
        },
        xAxis: {
          categories: getProblems(payload),
          title: T.wordsProblem,
          min: 0,
        },
        yAxis: {
          min: 0,
          max: 100,
          title: T.wordsPercentage,
        },
        tooltip: {},
        plotOptions: {
          bar: {
            dataLabels: {
              enabled: true,
              format: '{y} %',
            },
          },
        },
        series: [
          {
            name: T.wordsPercentage,
            data: gethighScoreCount(payload),
          },
        ],
      },
      lowScoreChartOptions: {
        chart: {
          type: 'bar',
        },
        title: {
          text: `${T.wordsStudentsScored} 0%`,
        },
        xAxis: {
          categories: getProblems(payload),
          title: T.wordsProblem,
          min: 0,
        },
        yAxis: {
          min: 0,
          max: 100,
          title: T.wordsPercentage,
        },
        tooltip: {},
        plotOptions: {
          bar: {
            dataLabels: {
              enabled: true,
              format: '{y} %',
            },
          },
        },
        series: [
          {
            name: T.wordsPercentage,
            data: getlowScoreCount(payload),
          },
        ],
      },
      maximumChartOptions: {
        chart: {
          type: 'bar',
        },
        title: {
          text: T.wordsMaximumScore,
        },
        xAxis: {
          categories: getProblems(payload),
          title: T.wordsProblem,
          min: 0,
        },
        yAxis: {
          min: 0,
          max: getMaxPoints(payload),
          title: T.wordsScore,
        },
        tooltip: {},
        plotOptions: {
          bar: {
            dataLabels: {
              enabled: true,
              format: '{y}',
            },
          },
        },
        series: [
          {
            name: T.wordsScore,
            data: getMaximum(payload),
          },
        ],
      },
      minimumChartOptions: {
        chart: {
          type: 'bar',
        },
        title: {
          text: T.wordsMinimumScore,
        },
        xAxis: {
          categories: getProblems(payload),
          title: T.wordsProblem,
          min: 0,
        },
        yAxis: {
          min: 0,
          max: getMaxPoints(payload),
          title: T.wordsScore,
        },
        tooltip: {},
        plotOptions: {
          bar: {
            dataLabels: {
              enabled: true,
              format: '{y}',
            },
          },
        },
        series: [
          {
            name: T.wordsScore,
            data: getMinimum(payload),
          },
        ],
      },
    },
    components: {
      'omegaup-course-statistics': course_Statistics,
    },
  });
});
