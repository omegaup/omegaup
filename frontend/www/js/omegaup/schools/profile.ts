import Vue from 'vue';

import * as api from '../api';
import { types } from '../api_types';
import school_Profile from '../components/schools/Profile.vue';
import { OmegaUp } from '../omegaup';
import { SchoolCoderOfTheMonth, SchoolUser } from '../types';
import * as ui from '../ui';

OmegaUp.on('ready', () => {
  const payload = types.payloadParsers.SchoolProfileDetailsPayload();
  const schoolProfile = new Vue({
    el: '#main-container',
    render: function(createElement) {
      return createElement('omegaup-school-profile', {
        props: {
          codersOfTheMonth: this.codersOfTheMonth,
          country: payload.country,
          monthlySolvedProblemsCount: this.monthlySolvedProblemsCount,
          name: payload.school_name,
          rank: payload.ranking,
          stateName: payload.state_name,
          users: this.users,
        },
      });
    },
    data: {
      codersOfTheMonth: <SchoolCoderOfTheMonth[]>[],
      monthlySolvedProblemsCount: <types.SchoolProblemsSolved[]>[],
      users: <SchoolUser[]>[],
    },
    components: {
      'omegaup-school-profile': school_Profile,
    },
  });

  api.School.schoolCodersOfTheMonth({
    school_id: payload.school_id,
  })
    .then(response => {
      schoolProfile.codersOfTheMonth = response.coders.map(
        coder => new SchoolCoderOfTheMonth(coder),
      );
    })
    .catch(ui.apiError);

  api.School.users({
    school_id: payload.school_id,
  })
    .then(response => {
      schoolProfile.users = response.users.map(user => new SchoolUser(user));
    })
    .catch(ui.apiError);

  api.School.monthlySolvedProblemsCount({
    school_id: payload.school_id,
  })
    .then(response => {
      schoolProfile.monthlySolvedProblemsCount =
        response.distinct_problems_solved;
    })
    .catch(ui.apiError);
});
