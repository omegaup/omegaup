<template>
  <div class="panel-body">
    <label><input type="radio"
           v-model="type"
           value="delta"> {{ T.profileStatisticsDelta }}</label> <label><input type="radio"
           v-model="type"
           value="cumulative"> {{ T.profileStatisticsCumulative }}</label> <label><input type=
           "radio"
           v-model="type"
           value="total"> {{ T.profileStatisticsTotal }}</label>
    <div class="period-group text-center"
         v-if="type != 'total' &amp;&amp; type != ''">
      <label><input name="period"
             type="radio"
             v-model="period"
             value="day"> {{ T.profileStatisticsDay }}</label> <label><input name="period"
             type="radio"
             v-model="period"
             value="week"> {{ T.profileStatisticsWeek }}</label> <label><input name="period"
             type="radio"
             v-model="period"
             value="month"> {{ T.profileStatisticsMonth }}</label> <label><input name="period"
             type="radio"
             v-model="period"
             value="year"> {{ T.profileStatisticsYear }}</label>
    </div>
    <div id="verdict-chart"></div>
  </div>
</template>

<script>
import {T} from '../../omegaup.js';
import UI from '../../ui.js';

export default {
  props: {data: Object, username: String},
  data: function() { return {T: T, UI: UI, type: 'delta', period: 'day'};},
  watch: {
    type: function(val) {
      if (val == 'total') {
        let self = this;
        let runs = self.normalizedRunCounts;
        // Removing all series, except last one, because here is where the
        // data will be placed. Otherwise, the chart will not be shown
        while (self.chart.series.length > 1) self.chart.series[0].remove(false);
        self.chart.update({
          chart: {
            plotBackgroundColor: null,
            plotBorderWidth: null,
            plotShadow: false,
            type: 'pie'
          },
          xAxis: {
            title: {text: ''},
          },
          yAxis: {
            title: {text: ''},
          },
          title: {
            text: omegaup.UI.formatString(omegaup.T.profileStatisticsVerdictsOf,
                                          {user: self.username})
          },
          tooltip: {pointFormat: '{series.name}: {point.y}'},
          plotOptions: {
            pie: {
              allowPointSelect: true,
              cursor: 'pointer',
              dataLabels: {
                enabled: true,
                color: '#000000',
                connectorColor: '#000000',
                format:
                    '<b>{point.name}</b>: {point.percentage:.1f} % ({point.y})',
              }
            }
          },
          series: [
            {
              name: omegaup.UI.formatString(omegaup.T.profileStatisticsRuns),
              data: runs
            }
          ]
        });
        self.chart.redraw();
      } else {
        this.verdictPeriodCounts();
      }
    },
    period: function(val) { this.verdictPeriodCounts();},
  },
  mounted: function() {
    let self = this;
    let runs = self.normalizedRunCountsForPeriod;
    let data = runs[self.type];
    self.chart = Highcharts.chart('verdict-chart', {
      chart: {type: 'column'},
      title: {
        text: omegaup.UI.formatString(omegaup.T.profileStatisticsVerdictsOf,
                                      {user: this.username})
      },
      xAxis: {
        categories: runs.categories,
        title: {text: omegaup.T.profileStatisticsPeriod},
        labels: {
          rotation: -45,
        }
      },
      yAxis: {
        min: 0,
        title: {text: omegaup.T.profileStatisticsNumberOfSolvedProblems},
        stackLabels: {
          enabled: false,
          style: {
            fontWeight: 'bold',
            color: (Highcharts.theme && Highcharts.theme.textColor) || 'gray'
          }
        }
      },
      legend: {
        align: 'right',
        x: -30,
        verticalAlign: 'top',
        y: 25,
        floating: true,
        backgroundColor:
            (Highcharts.theme && Highcharts.theme.background2) || 'white',
        borderColor: '#CCC',
        borderWidth: 1,
        shadow: false
      },
      tooltip: {
        headerFormat: '<b>{point.x}</b><br/>',
        pointFormat: '{series.name}: {point.y}<br/>Total: {point.stackTotal}'
      },
      plotOptions: {
        column: {
          stacking: 'normal',
          dataLabels: {
            enabled: false,
            color: (Highcharts.theme && Highcharts.theme.dataLabelsColor) ||
                       'white'
          }
        }
      },
      series: data
    });
  },
  computed: {
    totalRuns: function() {
      let self = this;
      let total = 0;
      for (let runs of self.data.runs) {
        total += parseInt(runs['runs']);
      }
      return total;
    },
    normalizedRunCounts: function() {
      let self = this;
      let total = self.totalRuns;
      let stats = self.data.runs;
      let runs = stats.reduce((total, amount) => {
        total[amount.verdict] += parseInt(amount.runs);
        return total;
      }, {WA: 0, PA: 0, AC: 0, TLE: 0, MLE: 0, OLE: 0, RTE: 0, CE: 0, JE: 0});
      let verdicts = Object.keys(runs);
      let response = [];
      for (let verdict of verdicts) {
        let numRuns = runs[verdict];
        if (verdict == 'AC') {
          response.push(
              {name: verdict, y: numRuns, sliced: true, selected: true});
        } else {
          response.push({name: verdict, y: numRuns});
        }
      }
      return response;
    },
    normalizedPeriodRunCounts: function() {
      let self = this;
      let runs = self.groupedPeriods;
      let periods = Object.keys(runs);
      let response = {};
      for (let period of periods) {
        response[period] = {
          categories: Object.keys(runs[period]),
          delta: [],
          cumulative: []
        };
        let verdicts = ['AC', 'PA', 'WA', 'TLE', 'RTE'];
        for (let verdict of verdicts) {
          runs[period][verdict] = 0;
        }
        for (let[index, verdict] of verdicts.entries()) {
          response[period].delta[index] = {name: verdict, data: []};
          response[period].cumulative[index] = {name: verdict, data: []};
          for (let[ind, date] of response[period].categories.entries()) {
            runs[period][verdict] += parseInt(runs[period][date][verdict]);
            response[period].delta[index]['data'][ind] =
                parseInt(runs[period][date][verdict]);
            response[period].cumulative[index]['data'][ind] =
                runs[period][verdict];
          }
        }
      }
      return response;
    },
    groupedPeriods: function() {
      let self = this;
      let stats = self.data.runs;
      let periods = ['day', 'week', 'month', 'year'];
      for (let[index, run] of stats.entries()) {
        for (let period of periods) {
          if (typeof stats[index][period] != 'undefined') break;
        }
        let date = new Date(run.date);
        let day = date.getDay();
        // group by days
        stats[index]['day'] = date.toLocaleDateString(T.locale);
        // group by weeks
        let diffMonday = date.getDate() - day + (day == 0 ? -6 : 1);
        let diffSunday = date.getDate() + (7 - day);
        let firstDay = new Date(date.setDate(diffMonday));
        let lastDay = new Date(date.setDate(diffSunday));
        stats[index]['week'] = firstDay.toLocaleDateString(T.locale) + ' - ' +
                               lastDay.toLocaleDateString(T.locale);
        // group by month
        stats[index]['month'] = run.date.substring(0, 7);
        // group by year
        stats[index]['year'] = run.date.substring(0, 4);
      }
      let periodStats = {};
      for (let period of periods) {
        periodStats[period] = stats.reduce(function(groups, item) {
          let val = item[period];
          groups[val] = groups[val] || {WA: 0, PA: 0, AC: 0, TLE: 0, RTE: 0};
          groups[val][item.verdict] += parseInt(item.runs);
          return groups;
        }, {});
      }
      return periodStats;
    },
    normalizedRunCountsForPeriod: function() {
      let self = this;
      return self.normalizedPeriodRunCounts[self.period];
    }
  },
  methods: {
    verdictPeriodCounts: function() {
      let self = this;
      let runs = self.normalizedRunCountsForPeriod;
      let data = runs[self.type];
      self.chart.update({
        chart: {type: 'column'},
        xAxis: {
          categories: runs.categories,
          title: {text: omegaup.T.profileStatisticsPeriod},
          labels: {
            rotation: -45,
          }
        },
        yAxis: {
          min: 0,
          title: {text: omegaup.T.profileStatisticsNumberOfSolvedProblems},
          stackLabels: {
            enabled: false,
            style: {
              fontWeight: 'bold',
              color: (Highcharts.theme && Highcharts.theme.textColor) || 'gray'
            }
          }
        },
        legend: {
          align: 'right',
          x: -30,
          verticalAlign: 'top',
          y: 25,
          floating: true,
          backgroundColor:
              (Highcharts.theme && Highcharts.theme.background2) || 'white',
          borderColor: '#CCC',
          borderWidth: 1,
          shadow: false
        },
        tooltip: {
          headerFormat: '<b>{point.x}</b><br/>',
          pointFormat: '{series.name}: {point.y}<br/>Total: {point.stackTotal}'
        },
        plotOptions: {
          column: {
            stacking: 'normal',
            dataLabels: {
              enabled: false,
              color: (Highcharts.theme && Highcharts.theme.dataLabelsColor) ||
                         'white'
            }
          }
        },
        series: []
      });
      // Removing old series
      while (self.chart.series.length) self.chart.series[0].remove(false);
      // Adding new series
      let numSeries = data.length;
      for (let i = 0; i < numSeries; i++) {
        self.chart.addSeries(data[i]);
      }
      self.chart.redraw();
    },
  }
};
</script>
