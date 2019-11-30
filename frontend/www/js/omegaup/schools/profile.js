import Vue from 'vue';
import { API, OmegaUp, UI } from '../omegaup.js';
import school_Profile from '../components/schools/Profile.vue';

OmegaUp.on('ready', function() {
  const payload = JSON.parse(document.getElementById('payload').innerText);
  let schoolProfile = new Vue({
    el: '#school-profile',
    render: function(createElement) {
      return createElement('omegaup-school-profile', {
        props: {
          name: this.name,
          country: this.country,
          stateName: this.stateName,
          monthlySolvedProblemsCount: this.monthlySolvedProblemsCount,
          users: this.users,
        },
      });
    },
    data: {
      name: payload.school_name,
      country: payload.country,
      stateName: payload.state_name,
      monthlySolvedProblemsCount: [],
      users: [],
    },
    components: {
      'omegaup-school-profile': school_Profile,
    },
  });

  API.School.schoolCodersOfTheMonth({
    school_id: payload.school_id
  }).then(function (data) {
    console.log(data);
  }).fail(UI.apiError);

  API.School.users({
    school_id: payload.school_id
  }).then(function (data) {
      schoolProfile.users = data.users;
  }).fail(UI.apiError);

  API.School.monthlySolvedProblemsCount({
    school_id: payload.school_id,
    months_count: 6,
  }).then(function(data) {
    schoolProfile.monthlySolvedProblemsCount = data.distinct_problems_solved;
  }).fail(UI.apiError);
});
