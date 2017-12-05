<template>
  <div class="panel-body">
    <label><input type="radio"
           v-model="type"
           v-on:click="onSelectPeriod"
           value="delta"> {{ T.profileStatisticsDelta }}</label> <label><input type="radio"
           v-model="type"
           v-on:click="onSelectPeriod"
           value="cumulative"> {{ T.profileStatisticsCumulative }}</label> <input id="total"
         type="radio"
         v-model="type"
         v-on:click="onSelectTypeTotals"
         value="total"> <label for="total">{{ T.profileStatisticsTotal }}</label>
    <div class="period_group text-center"
         v-if="type != 'total' &amp;&amp; type != ''">
      <input id="day"
           name="period"
           type="radio"
           v-model="period"
           v-on:click="onSelectPeriod"
           value="day"> <label for="day">{{ T.profileStatisticsDay }}</label> <input id="week"
           name="period"
           type="radio"
           v-model="period"
           v-on:click="onSelectPeriod"
           value="week"> <label for="week">{{ T.profileStatisticsWeek }}</label> <input id="month"
           name="period"
           type="radio"
           v-model="period"
           v-on:click="onSelectPeriod"
           value="month"> <label for="month">{{ T.profileStatisticsMonth }}</label> <input id=
           "year"
           name="period"
           type="radio"
           v-model="period"
           v-on:click="onSelectPeriod"
           value="year"> <label for="year">{{ T.profileStatisticsYear }}</label>
    </div>
    <div v-show="type == 'total'">
      <div id="verdict-chart"></div>
    </div>
    <div v-show="type != 'total'">
      <div id="verdict-period-chart"></div>
    </div>
  </div>
</template>

<script>
import {T} from '../../omegaup.js';
import UI from '../../ui.js';

export default {
  props: {data: Object, username: String},
  data: function() { return {T: T, UI: UI, type: '', period: 'day'};},
  computed: {
    verdictPeriodCounts: function() {
      let runs = this.normalizePeriodRunCounts;
      let data = runs[this.type];
      return new Highcharts.Chart({
        chart: {type: 'column', renderTo: 'verdict-period-chart'},
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
    verdictCounts: function() {
      console.log('Entra a verdictCounts');
      let runs = this.normalizeRunCounts;
      return new Highcharts.Chart({
        chart: {
          plotBackgroundColor: null,
          plotBorderWidth: null,
          plotShadow: false,
          renderTo: 'verdict-chart'
        },
        title: {
          text: omegaup.UI.formatString(omegaup.T.profileStatisticsVerdictsOf,
                                        {user: this.username})
        },
        tooltip: {
          formatter: function() {
            return omegaup.UI.formatString(
                omegaup.T.profileStatisticsRuns,
                {runs: runs.runs[this.point.name].count});
          }
        },
        plotOptions: {
          pie: {
            allowPointSelect: true,
            cursor: 'pointer',
            dataLabels: {
              enabled: true,
              color: '#000000',
              connectorColor: '#000000',
              formatter: function() {
                return '<b>' + this.point.name + '</b>: ' +
                       this.percentage.toFixed(2) + ' % (' +
                       runs.runs[this.point.name].count + ')';
              }
            }
          }
        },
        series: [{type: 'pie', name: 'Proporción', data: runs.percentage}]
      });
    },
    normalizeRunCounts: function() {
      let total = this.countRuns(this.data.runs);
      let runs = this.groupRuns(this.data.runs, 'verdict');
      let verdicts = ['WA', 'PA', 'AC', 'TLE', 'MLE', 'OLE', 'RTE', 'CE', 'JE'];
      let response = {runs: {}, percentage: []};
      for (let[index, verdict] of verdicts.entries()) {
        let num_runs =
            typeof runs[verdict] == 'undefined' ? 0 : runs[verdict][verdict];
        response['runs'][verdict] = {name: verdict, count: num_runs};
        if (verdict == 'AC') {
          response['percentage'][index] = {
            name: 'AC',
            y: (num_runs / total) * 100,
            sliced: true,
            selected: true
          };
        } else {
          response['percentage'][index] = [verdict, (num_runs / total) * 100];
        }
      }
      return response;
    },
    normalizePeriodRunCounts: function() {
      let runs =
          this.groupRuns(this.createPeriodGroup(this.data.runs), this.period);
      let response = {categories: Object.keys(runs), delta: [], cumulative: []};
      let verdicts = ['AC', 'PA', 'WA', 'TLE', 'RTE'];
      for (let verdict of verdicts) {
        runs[verdict] = 0;
      }
      for (let[index, verdict] of verdicts.entries()) {
        response.delta[index] = {name: verdict, data: []};
        response.cumulative[index] = {name: verdict, data: []};
        for (let[ind, date] of response.categories.entries()) {
          runs[verdict] += parseInt(runs[date][verdict]);
          response.delta[index]['data'][ind] = parseInt(runs[date][verdict]);
          response.cumulative[index]['data'][ind] = runs[verdict];
        }
      }
      return response;
    }
  },
  methods: {
    onSelectTypeTotals: function() { this.verdictCounts;},
    onSelectPeriod: function() { this.verdictPeriodCounts;},
    countRuns: function(stats) {
      let total = 0;
      for (let runs of stats) {
        total += parseInt(runs['runs']);
      }
      return total;
    },
    groupRuns: function(stats, prop) {
      return stats.reduce(function(groups, item) {
        let val = item[prop];
        groups[val] =
            groups[val] ||
            {WA: 0, PA: 0, AC: 0, TLE: 0, MLE: 0, OLE: 0, RTE: 0, CE: 0, JE: 0};
        groups[val][item.verdict] += parseInt(item.runs);
        return groups;
      }, {});
    },
    createPeriodGroup: function(stats) {
      for (let[index, run] of stats.entries()) {
        if (typeof stats[index]['day'] != 'undefined') break;
        if (typeof stats[index]['week'] != 'undefined') break;
        if (typeof stats[index]['month'] != 'undefined') break;
        if (typeof stats[index]['year'] != 'undefined') break;
        let date = new Date(run.date);
        let day = date.getDay();
        // group by days
        date.getDate() + 1;
        stats[index]['day'] = date.toLocaleDateString(T.locale);
        // group by weeks
        let diff_monday = date.getDate() - day + (day == 0 ? -6 : 1);
        let diff_sunday = date.getDate() + (7 - day);
        let first_day = new Date(date.setDate(diff_monday));
        let last_day = new Date(date.setDate(diff_sunday));
        stats[index]['week'] = first_day.toLocaleDateString(T.locale) + ' - ' +
                               last_day.toLocaleDateString(T.locale);
        // group by month
        stats[index]['month'] = run.date.substring(0, 7);
        // group by year
        stats[index]['year'] = run.date.substring(0, 4);
      }
      return stats;
    },
  },
  components: {},
};
</script>
