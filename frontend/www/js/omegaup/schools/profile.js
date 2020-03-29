import Vue from 'vue';
import { OmegaUp } from '../omegaup';
import API from '../api.js';
import * as UI from '../ui';
import school_Profile from '../components/schools/Profile.vue';

OmegaUp.on('ready', function() {
  const payload = JSON.parse(document.getElementById('payload').innerText);
  let schoolProfile = new Vue({
    el: '#school-profile',
    render: function(createElement) {
      return createElement('omegaup-school-profile', {
        props: {
          codersOfTheMonth: this.codersOfTheMonth,
          country: this.country,
          monthlySolvedProblemsCount: this.monthlySolvedProblemsCount,
          name: this.name,
          rank: this.rank,
          stateName: this.stateName,
          users: this.users,
        },
      });
    },
    data: {
      codersOfTheMonth: [],
      country: payload.country,
      monthlySolvedProblemsCount: [],
      name: payload.school_name,
      rank: payload.ranking,
      stateName: payload.state_name,
      users: [],
    },
    components: {
      'omegaup-school-profile': school_Profile,
    },
  });

  API.School.schoolCodersOfTheMonth({
    school_id: payload.school_id,
  })
    .then(function(data) {
      schoolProfile.codersOfTheMonth = data.coders;
    })
    .catch(UI.apiError);

  API.School.users({
    school_id: payload.school_id,
  })
    .then(function(data) {
      schoolProfile.users = data.users;
    })
    .catch(UI.apiError);

  API.School.monthlySolvedProblemsCount({
    school_id: payload.school_id,
  })
    .then(function(data) {
      schoolProfile.monthlySolvedProblemsCount = data.distinct_problems_solved;
    })
    .catch(UI.apiError);
});
