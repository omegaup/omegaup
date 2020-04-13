import common_Index from '../components/common/Index.vue';
import { OmegaUp } from '../omegaup';
import { types } from '../api_types';
import T from '../lang';
import Vue from 'vue';

OmegaUp.on('ready', () => {
  const payload = types.payloadParsers.IndexPayload('payload');

  let coderOfTheMonthData = null;
  if (payload.coderOfTheMonthData !== null) {
    coderOfTheMonthData = payload.coderOfTheMonthData;
  }

  const ranking = payload.userRank.map((user, index) => ({
    rank: index + 1,
    country: user.country_id,
    username: user.username,
    classname: user.classname,
    score: user.score,
    problems_solved: user.problems_solved,
  }));

  const commonIndex = new Vue({
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
        },
      });
    },
    data: {
      coderOfTheMonth: coderOfTheMonthData ? coderOfTheMonthData.all : null,
      coderOfTheMonthFemale: coderOfTheMonthData ? coderOfTheMonthData.female : null,
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
        totalRows: 5,
      },
      enableSocialMediaResources: payload.enableSocialMediaResources,
      schoolOfTheMonthData: payload.schoolOfTheMonthData,
      upcomingContests: payload.upcomingContests.results,
    },
    components: {
      'omegaup-common-index': common_Index,
    },
  });
});
