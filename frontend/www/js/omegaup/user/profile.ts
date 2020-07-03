import Vue from 'vue';
import user_Profile from '../components/user/Profile.vue';
import Highcharts from 'highcharts-vue';
import { OmegaUp } from '../omegaup';
import T from '../lang';
import * as api from '../api';
import * as ui from '../ui';
import { types } from '../api_types';
import { Problem, ContestResult } from '../linkable_resource';

OmegaUp.on('ready', function() {
  const payload = types.payloadParsers.UserProfileDetailsPayload();
  const viewProfile = new Vue({
    el: '#main-container',
    render: function(createElement) {
      return createElement('omegaup-user-profile', {
        props: {
          profile: payload.profile,
          contests: Object.values(payload.contests)
            .map(contest => {
              const now = new Date();
              if (contest.place === null || now <= contest.data.finish_time) {
                return null;
              }
              return new ContestResult(contest);
            })
            .filter(contest => !!contest),
          solvedProblems: payload.solvedProblems.map(
            problem => new Problem(problem),
          ),
          unsolvedProblems: payload.unsolvedProblems.map(
            problem => new Problem(problem),
          ),
          createdProblems: payload.createdProblems.map(
            problem => new Problem(problem),
          ),
          visitorBadges: <Set<string>>new Set(payload.badges),
          profileBadges: <Set<string>>(
            new Set(payload.ownedBadges.map(badge => badge.badge_alias))
          ),
          rank: this.rank,
          programmingLanguages: payload.programmingLanguages,
          charts: payload.stats,
          periodStatisticOptions: {
            title: {
              text: ui.formatString(T.profileStatisticsVerdictsOf, {
                user: payload.profile.username,
              }),
            },
            chart: { type: 'column' },
            xAxis: {
              categories: [],
              title: { text: T.profileStatisticsPeriod },
              labels: {
                rotation: -45,
              },
            },
            yAxis: {
              min: 0,
              title: { text: T.profileStatisticsNumberOfSolvedProblems },
              stackLabels: {
                enabled: false,
                style: {
                  fontWeight: 'bold',
                  color: 'gray',
                },
              },
            },
            legend: {
              align: 'right',
              x: -30,
              verticalAlign: 'top',
              y: 25,
              floating: true,
              backgroundColor: 'white',
              borderColor: '#CCC',
              borderWidth: 1,
              shadow: false,
            },
            tooltip: {
              headerFormat: '<b>{point.x}</b><br/>',
              pointFormat:
                '{series.name}: {point.y}<br/>Total: {point.stackTotal}',
            },
            plotOptions: {
              column: {
                stacking: 'normal',
                dataLabels: {
                  enabled: false,
                  color: 'white',
                },
              },
            },
            series: [],
          },
          aggregateStatisticOptions: {
            title: {
              text: ui.formatString(T.profileStatisticsVerdictsOf, {
                user: payload.profile.username,
              }),
            },
            chart: {
              plotBackgroundColor: null,
              plotBorderWidth: null,
              plotShadow: false,
              type: 'pie',
            },
            xAxis: {
              title: { text: '' },
            },
            yAxis: {
              title: { text: '' },
            },
            tooltip: { pointFormat: '{series.name}: {point.y}' },
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
                },
              },
            },
            series: [
              {
                name: T.profileStatisticsRuns,
                data: [],
              },
            ],
          },
        },
        on: {
          'update-period-statistics': (e, categories, data) => {
            e.periodStatisticOptions.xAxis.categories = categories;
            e.periodStatisticOptions.series = data;
          },
          'update-aggregate-statistics': e =>
            (e.aggregateStatisticOptions.series[0].data =
              e.normalizedRunCounts),
        },
      });
    },

    computed: {
      rank: function() {
        switch (payload.profile.classname) {
          case 'user-rank-unranked':
            return T.profileRankUnrated;
          case 'user-rank-beginner':
            return T.profileRankBeginner;
          case 'user-rank-specialist':
            return T.profileRankSpecialist;
          case 'user-rank-expert':
            return T.profileRankExpert;
          case 'user-rank-master':
            return T.profileRankMaster;
          case 'user-rank-international-master':
            return T.profileRankInternationalMaster;
        }
      },
    },
    components: {
      'omegaup-user-profile': user_Profile,
    },
  });
});
