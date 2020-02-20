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
