import common_Index from '../components/common/Index.vue';
import { API, UI, OmegaUp, T } from '../omegaup.js';
import omegaup from '../api.js';
import Vue from 'vue';

OmegaUp.on('ready', function() {
  const payload = JSON.parse(document.getElementById('payload').innerText);
  let coderOfTheMonthData = null;
  const emptyRank = {
    page: 0,
    length: 0,
    isIndex: true,
    isLogged: false,
    availableFilters: [],
    filter: '',
    ranking: [],
    resultTotal: 0,
  };
  const runsChart = payload.runsChartPayload;
  const minY = runsChart.total.length === 0 ? 0 : runsChart.total[0] / 2.0;
  if (payload.coderOfTheMonthData !== null) {
    coderOfTheMonthData = {
      username: payload.coderOfTheMonthData.username,
      classname: payload.coderOfTheMonthData.classname,
      name: payload.coderOfTheMonthData.name,
      country: payload.coderOfTheMonthData.country,
      country_id: payload.coderOfTheMonthData.country_id,
      state: payload.coderOfTheMonthData.state,
      school: payload.coderOfTheMonthData.school,
      gravatar_92: payload.coderOfTheMonthData.gravatar_92,
    };
  }
  let commonIndex = new Vue({
    el: '#common-index',
    render: function(createElement) {
      return createElement('omegaup-common-index', {
        props: {
          coderOfTheMonth: this.coderOfTheMonth,
          currentUserInfo: this.currentUserInfo,
          rankTable: this.rankTable,
          schoolsRank: this.schoolsRank,
          enableSocialMediaResources: this.enableSocialMediaResources,
          schoolOfTheMonthData: this.schoolOfTheMonthData,
          upcomingContests: this.upcomingContests,
          chartOptions: this.chartOptions,
        },
      });
    },
    data: {
      coderOfTheMonth: coderOfTheMonthData,
      currentUserInfo: payload.currentUserInfo,
      rankTable: emptyRank,
      schoolsRank: {
        page: payload.schoolRankPayload.page,
        length: payload.schoolRankPayload.length,
        showHeader: payload.schoolRankPayload.showHeader,
        rank: [],
        totalRows: 0,
      },
      enableSocialMediaResources: payload.enableSocialMediaResources,
      schoolOfTheMonthData: payload.schoolOfTheMonthData,
      upcomingContests: [],
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

  API.User.rankByProblemsSolved({
    offset: payload.rankTablePayload.page,
    rowcount: payload.rankTablePayload.length,
    filter: payload.rankTablePayload.filter,
  })
    .then(result => {
      const ranking = [];
      for (const user of result.rank) {
        ranking.add({
          rank: user.rank,
          country: user.country_id,
          username: user.username,
          classname: user.classname,
          name: user.name,
          score: user.score,
          problemsSolvedUser: 0,
        });
      }

      commonIndex.rankTable = {
        page: payload.rankTablePayload.page,
        length: payload.rankTablePayload.length,
        isIndex: payload.rankTablePayload.isIndex,
        isLogged: payload.rankTablePayload.isLogged,
        availableFilters: payload.rankTablePayload.availableFilters,
        filter: payload.rankTablePayload.filter,
        ranking: ranking,
        resultTotal: parseInt(result.total),
      };
    })
    .fail(UI.apiError);

  API.School.schoolsOfTheMonth({
    rowcount: 5,
  })
    .then(data => {
      commonIndex.schoolsRank.rank = data.rank;
    })
    .fail(UI.apiError);

  API.Contest.list({ active: 'ACTIVE' })
    .then(data => {
      commonIndex.upcomingContests = data.results;
    })
    .fail(UI.apiError);
});
