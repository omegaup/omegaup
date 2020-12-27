import Vue from 'vue';
import user_Profile from '../components/user/Profile.vue';
import { OmegaUp } from '../omegaup-legacy';
import T from '../lang';
import * as api from '../api';
import * as ui from '../ui';
import { Problem, ContestResult } from '../linkable_resource';

OmegaUp.on('ready', function () {
  const payload = JSON.parse(document.getElementById('payload').innerText);
  const profile = payload.payload;
  let viewProfile = new Vue({
    el: '#user-profile',
    render: function (createElement) {
      return createElement('omegaup-user-profile', {
        props: {
          profile: this.profile,
          contests: this.contests,
          solvedProblems: this.solvedProblems,
          unsolvedProblems: this.unsolvedProblems,
          createdProblems: this.createdProblems,
          visitorBadges: this.visitorBadges,
          profileBadges: this.profileBadges,
          rank: this.rank,
          charts: this.charts,
          periodStatisticOptions: this.periodStatisticOptions,
          aggregateStatisticOptions: this.aggregateStatisticOptions,
        },
        on: {
          'update-period-statistics': (e, categories, data) => {
            e.periodStatisticOptions.xAxis.categories = categories;
            e.periodStatisticOptions.series = data;
          },
          'update-aggregate-statistics': (e) =>
            (e.aggregateStatisticOptions.series[0].data =
              e.normalizedRunCounts),
        },
      });
    },
    data: {
      profile: profile,
      contests: [],
      profileBadges: new Set(),
      solvedProblems: [],
      unsolvedProblems: [],
      createdProblems: [],
      visitorBadges: new Set(),
      charts: null,
      periodStatisticOptions: {
        title: {
          text: ui.formatString(T.profileStatisticsVerdictsOf, {
            user: profile.username,
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
              color: (Highcharts.theme && Highcharts.theme.textColor) || 'gray',
            },
          },
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
          shadow: false,
        },
        tooltip: {
          headerFormat: '<b>{point.x}</b><br/>',
          pointFormat: '{series.name}: {point.y}<br/>Total: {point.stackTotal}',
        },
        plotOptions: {
          column: {
            stacking: 'normal',
            dataLabels: {
              enabled: false,
              color:
                (Highcharts.theme && Highcharts.theme.dataLabelsColor) ||
                'white',
            },
          },
        },
        series: [],
      },
      aggregateStatisticOptions: {
        title: {
          text: ui.formatString(T.profileStatisticsVerdictsOf, {
            user: profile.username,
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
        title: {
          text: ui.formatString(T.profileStatisticsVerdictsOf, {
            user: profile.username,
          }),
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
    computed: {
      rank: function () {
        switch (profile.classname) {
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

  api.User.contestStats({ username: profile.username })
    .then((result) => {
      const now = new Date();
      viewProfile.contests = Object.values(result.contests)
        .filter((contest) => contest.place && now > contest.data.finish_time)
        .map((contest) => new ContestResult(contest));
    })
    .catch(ui.apiError);

  api.User.problemsSolved({ username: profile.username })
    .then((result) => {
      viewProfile.solvedProblems = result.problems.map(
        (problem) => new Problem(problem),
      );
    })
    .catch(ui.apiError);

  api.User.listUnsolvedProblems({ username: profile.username })
    .then((result) => {
      viewProfile.unsolvedProblems = result.problems.map(
        (problem) => new Problem(problem),
      );
    })
    .catch(ui.apiError);

  api.User.problemsCreated({ username: profile.username })
    .then((result) => {
      viewProfile.createdProblems = result.problems.map(
        (problem) => new Problem(problem),
      );
    })
    .catch(ui.apiError);

  if (payload.logged_in) {
    api.Badge.myList({})
      .then((result) => {
        viewProfile.visitorBadges = new Set(
          result.badges.map((badge) => badge.badge_alias),
        );
      })
      .catch(ui.apiError);
  }

  api.Badge.userList({ target_username: profile.username })
    .then((result) => {
      viewProfile.profileBadges = new Set(
        result.badges.map((badge) => badge.badge_alias),
      );
    })
    .catch(ui.apiError);

  api.User.stats({ username: profile.username })
    .then((result) => {
      viewProfile.charts = result;
    })
    .catch(ui.apiError);
});
