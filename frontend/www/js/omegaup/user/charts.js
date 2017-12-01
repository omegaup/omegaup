import user_Charts from '../components/user/Charts.vue';
import {API, UI, OmegaUp, T} from '../omegaup.js';
import Vue from 'vue';

OmegaUp.on('ready', function() {
  var username = $('#username').attr('data-username');
  var stats_data = null;
  omegaup.API.User.stats({username: username})
      .then(function(data) {
        stats_data = data;
        verdictCounts('verdict-chart', username, stats_data);
      })
      .fail(omegaup.UI.apiError);

  let userCharts = new Vue({
    el: '#omegaup-user-charts',
    render: function(createElement) {
      return createElement('omegaup-user-charts', {
        props: {},
        on: {
          'select-type': function(type, period) {
            if (type == 'total') {
              verdictCounts('verdict-chart', username, stats_data);
            } else {
              var period_selected = 'day';
              verdictPeriodCounts('verdict-chart', username, stats_data, type,
                                  period);
            }
          },
          'select-period': function(type, period) {
            verdictPeriodCounts('verdict-chart', username, stats_data, type,
                                period);
          }
        }
      });
    },
    components: {
      'omegaup-user-charts': user_Charts,
    },
  });

  function verdictCounts(renderTo, title, stats) {
    var runs = normalizeRunCounts(stats);
    return new Highcharts.Chart({
      chart: {
        plotBackgroundColor: null,
        plotBorderWidth: null,
        plotShadow: false,
        renderTo: renderTo
      },
      title: {
        text: omegaup.UI.formatString(omegaup.T.profileStatisticsVerdictsOf,
                                      {user: title})
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
  }

  function verdictPeriodCounts(renderTo, title, stats, type, period) {
    let runs = normalizePeriodRunCounts(stats, period);
    var data = runs[type];
    return new Highcharts.Chart({
      chart: {type: 'column', renderTo: renderTo},
      title: {
        text: omegaup.UI.formatString(omegaup.T.profileStatisticsVerdictsOf,
                                      {user: title})
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
  }

  function normalizeRunCounts(stats) {
    var total = countRuns(stats.runs);
    var runs = groupRuns(stats.runs, 'verdict');
    var verdicts = ['WA', 'PA', 'AC', 'TLE', 'MLE', 'OLE', 'RTE', 'CE', 'JE'];
    var response = {runs: {}, percentage: []};
    for (var [index, verdict] of verdicts.entries()) {
      var num_runs =
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
  }

  function countRuns(stats) {
    var total = 0;
    for (var runs of stats) {
      total += parseInt(runs['runs']);
    }
    return total;
  }

  function groupRuns(stats, prop) {
    return stats.reduce(function(groups, item) {
      var val = item[prop];
      groups[val] =
          groups[val] ||
          {WA: 0, PA: 0, AC: 0, TLE: 0, MLE: 0, OLE: 0, RTE: 0, CE: 0, JE: 0};
      if (item.verdict == 'WA') groups[val].WA += parseInt(item.runs);
      if (item.verdict == 'PA') groups[val].PA += parseInt(item.runs);
      if (item.verdict == 'AC') groups[val].AC += parseInt(item.runs);
      if (item.verdict == 'TLE') groups[val].TLE += parseInt(item.runs);
      if (item.verdict == 'MLE') groups[val].MLE += parseInt(item.runs);
      if (item.verdict == 'OLE') groups[val].OLE += parseInt(item.runs);
      if (item.verdict == 'RTE') groups[val].RTE += parseInt(item.runs);
      if (item.verdict == 'CE') groups[val].CE += parseInt(item.runs);
      if (item.verdict == 'JE') groups[val].JE += parseInt(item.runs);
      return groups;
    }, {});
  }

  function normalizePeriodRunCounts(stats, period) {
    var runs = groupRuns(createPeriodGroup(stats.runs), period);
    var response = {categories: Object.keys(runs), delta: [], cumulative: []};
    var verdicts = ['AC', 'PA', 'WA', 'TLE', 'RTE'];
    for (verdict of verdicts) {
      runs[verdict] = 0;
    }
    for (var [index, verdict] of verdicts.entries()) {
      response.delta[index] = {name: verdict, data: []};
      response.cumulative[index] = {name: verdict, data: []};
      for (var [ind, date] of response.categories.entries()) {
        runs[verdict] += parseInt(runs[date][verdict]);
        response.delta[index]['data'][ind] = parseInt(runs[date][verdict]);
        response.cumulative[index]['data'][ind] = runs[verdict];
      }
    }
    return response;
  }

  function createPeriodGroup(stats) {
    for (let[index, run] of stats.entries()) {
      if (typeof stats[index]['day'] != 'undefined') break;
      if (typeof stats[index]['week'] != 'undefined') break;
      if (typeof stats[index]['month'] != 'undefined') break;
      if (typeof stats[index]['year'] != 'undefined') break;
      var date = new Date(run.date);
      var day = date.getDay();
      // group by days
      date.getDate() + 1;
      stats[index]['day'] = date.toLocaleDateString('es-MX');
      // group by weeks
      var diff_monday = date.getDate() - day + (day == 0 ? -6 : 1);
      var diff_sunday = date.getDate() + (7 - day);
      var first_day = new Date(date.setDate(diff_monday));
      var last_day = new Date(date.setDate(diff_sunday));
      stats[index]['week'] = first_day.toLocaleDateString('es-MX') + ' - ' +
                             last_day.toLocaleDateString('es-MX');
      // group by month
      stats[index]['month'] = run.date.substring(0, 7);
      // group by year
      stats[index]['year'] = run.date.substring(0, 4);
    }
    return stats;
  }
});
