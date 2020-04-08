import common_Index from '../components/common/Index.vue';
import { OmegaUp } from '../omegaup';
import T from '../lang';
import * as UI from '../ui';
import Vue from 'vue';

OmegaUp.on('ready', function() {
  const payload = JSON.parse(document.getElementById('payload').innerText);
  let coderOfTheMonthData = null;
  const ranking = [];
  for (const user of payload.rankTable.rank) {
    ranking.add({
      rank: user.ranking,
      country: user.country_id,
      username: user.username,
      classname: user.classname,
      name: user.name,
      score: user.score,
      problemsSolvedUser: 0,
    });
  }
  const runsChart = payload.runsChartPayload;
  const minY = runsChart.total.length === 0 ? 0 : runsChart.total[0] / 2.0;
  if (payload.coderOfTheMonthData !== null) {
    coderOfTheMonthData = payload.coderOfTheMonthData;
  }
  let commonIndex = new Vue({
    el: '#common-index',
    render: function(createElement) {
      return createElement('omegaup-common-index', {
        props: {
          coderOfTheMonth: this.coderOfTheMonth,
          coderOfTheMonthFemale: this.coderOfTheMonthFemale,
          currentUserInfo: this.currentUserInfo,
          rankTable: this.rankTable,
          schoolsRank: this.schoolsRank,
          enableSocialMediaResources: this.enableSocialMediaResources,
          schoolOfTheMonth: this.schoolOfTheMonthData,
          upcomingContests: this.upcomingContests,
          chartOptions: this.chartOptions,
        },
      });
    },
    data: {
      coderOfTheMonth: coderOfTheMonthData.all,
      coderOfTheMonthFemale: coderOfTheMonthData.female,
      currentUserInfo: payload.currentUserInfo,
      rankTable: {
        page: 1,
        length: 5,
        isIndex: true,
        isLogged: false,
        availableFilters: [],
        filter: '',
        ranking: ranking,
        resultTotal: payload.rankTable.total,
      },
      schoolsRank: {
        page: 1,
        length: 5,
        showHeader: true,
        rank: payload.schoolRank,
        totalRows: 5,
      },
      enableSocialMediaResources: payload.enableSocialMediaResources,
      schoolOfTheMonthData: payload.schoolOfTheMonthData,
      upcomingContests: payload.upcomingContests.results,
      chartOptions: {
        chart: {
          type: 'area',
          height: 300,
          spacingTop: 20,
        },
        title: { text: T.wordsTotalRuns },
        xAxis: {
          type: 'datetime',
          title: { text: null },
          categories: runsChart.date.reverse(),
        },
        yAxis: { title: { text: T.wordsRuns }, min: minY },
        legend: { enabled: false },
        plotOptions: {
          area: {
            lineWidth: 1,
            marker: { enabled: false },
            shadow: false,
            states: { hover: { lineWidth: 1 } },
            threshold: null,
          },
        },
        series: [
          {
            type: 'area',
            name: T.wordsRuns,
            data: runsChart.total.reverse(),
            fillColor: {
              linearGradient: { x1: 0, y1: 0, x2: 0, y2: 1 },
              stops: [
                [0, Highcharts.getOptions().colors[0]],
                [
                  1,
                  Highcharts.Color(Highcharts.getOptions().colors[0])
                    .setOpacity(0)
                    .get('rgba'),
                ],
              ],
            },
          },
        ],
      },
    },
    components: {
      'omegaup-common-index': common_Index,
    },
  });
});
