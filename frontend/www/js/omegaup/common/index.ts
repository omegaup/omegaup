import Homepage from '../components/homepage/Homepage.vue';
import { OmegaUp } from '../omegaup';
import { types } from '../api_types';
import * as api from '../api';
import * as ui from '../ui';
import T from '../lang';
import Vue from 'vue';

OmegaUp.on('ready', () => {
  const payload = types.payloadParsers.IndexPayload();
  const commonPayload = types.payloadParsers.CommonPayload();

  const ranking = payload.userRank.map((user, index) => ({
    rank: index + 1,
    country: user.country_id,
    username: user.username,
    classname: user.classname,
    score: user.score,
    problems_solved: user.problems_solved,
  }));

  const fromLogin =
    new URL(document.location.toString()).searchParams.get('fromLogin') !==
    null;

  new Vue({
    el: '#main-container',
    components: {
      'omegaup-homepage': Homepage,
    },
    render: function (createElement) {
      return createElement('omegaup-homepage', {
        props: {
          coderOfTheMonth: payload.coderOfTheMonthData
            ? payload.coderOfTheMonthData.all
            : null,
          coderOfTheMonthFemale: payload.coderOfTheMonthData
            ? payload.coderOfTheMonthData.female
            : null,
          currentUserInfo: payload.currentUserInfo,
          rankTable: {
            page: 1,
            length: 5,
            isIndex: true,
            isLogged: false,
            availableFilters: [],
            filter: '',
            ranking: ranking,
            resultTotal: ranking.length,
          },
          schoolsRank: {
            page: 1,
            length: 5,
            showHeader: true,
            rank: payload.schoolRank,
            totalRows: payload.schoolRank.length,
          },
          schoolOfTheMonth: payload.schoolOfTheMonthData,
          fromLogin: fromLogin,
          userTypes: commonPayload.userTypes,
        },
        on: {
          'update-user-objectives': ({
            hasCompetitiveObjective,
            hasLearningObjective,
            hasScholarObjective,
            hasTeachingObjective,
          }: {
            hasCompetitiveObjective: string;
            hasLearningObjective: string;
            hasScholarObjective: string;
            hasTeachingObjective: string;
          }) => {
            api.User.update({
              has_competitive_objective: hasCompetitiveObjective,
              has_learning_objective: hasLearningObjective,
              has_scholar_objective: hasScholarObjective,
              has_teaching_objective: hasTeachingObjective,
            })
              .then(() => {
                ui.success(T.userObjectivesUpdateSuccess);
              })
              .catch(ui.apiError);
          },
        },
      });
    },
  });

  if (fromLogin && commonPayload.userTypes.length === 0) {
    $('.objectivesQuestionsModal').modal();
  }
});
