import Vue from 'vue';
import school_Profile from '../components/schools/Profile.vue';
import { types } from '../api_types';
import { OmegaUp } from '../omegaup';
import * as ui from '../ui';
import T from '../lang';
import { SchoolCoderOfTheMonth, SchoolUser } from '../linkable_resource';

OmegaUp.on('ready', () => {
  const payload = types.payloadParsers.SchoolProfileDetailsPayload();

  const solvedProblemsCountData = payload.monthly_solved_problems.map(
    (solvedProblemsCount) => solvedProblemsCount.problems_solved,
  );
  const solvedProblemsCountCategories = payload.monthly_solved_problems.map(
    (solvedProblemsCount) =>
      `${solvedProblemsCount.year}-${solvedProblemsCount.month}`,
  );

  new Vue({
    el: '#main-container',
    components: {
      'omegaup-school-profile': school_Profile,
    },
    render: function (createElement) {
      return createElement('omegaup-school-profile', {
        props: {
          codersOfTheMonth: payload.coders_of_the_month.map(
            (coder) => new SchoolCoderOfTheMonth(coder),
          ),
          country: payload.country,
          name: payload.school_name,
          rank: payload.ranking,
          stateName: payload.state_name,
          users: payload.school_users.map((user) => new SchoolUser(user)),
          chartOptions: {
            chart: {
              type: 'line',
            },
            title: {
              text: ui.formatString(T.profileSchoolMonthlySolvedProblemsCount, {
                school: payload.school_name,
              }),
            },
            yAxis: {
              min: 0,
              title: {
                text: T.profileSolvedProblems,
              },
            },
            xAxis: {
              categories: solvedProblemsCountCategories,
              title: {
                text: T.wordsMonths,
              },
              labels: {
                rotation: -45,
              },
            },
            legend: {
              enabled: false,
            },
            tooltip: {
              headerFormat: '',
              pointFormat: '<b>{point.y}<b/>',
            },
            series: [
              {
                data: solvedProblemsCountData,
              },
            ],
          },
        },
      });
    },
  });
});
