import Homepage from '../components/homepage/Homepage.vue';
import { OmegaUp } from '../omegaup';
import { types } from '../api_types';
import Vue from 'vue';
import * as ui from '../ui';
import T from '../lang';

OmegaUp.on('ready', () => {
  const payload = types.payloadParsers.IndexPayload();

  const ranking = payload.userRank.map((user, index) => ({
    rank: index + 1,
    country: user.country_id,
    username: user.username,
    classname: user.classname,
    score: user.score,
    problems_solved: user.problems_solved,
  }));

  if (payload.parentalVerificationToken) {
    ui.success(T.parentalTokenVerificationSuccessful);
  }

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
        },
      });
    },
  });
});
