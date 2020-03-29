import { OmegaUp } from '../omegaup';
import API from '../api.js';
import * as UI from '../ui';
import T from '../lang';
import Vue from 'vue';
import schoolOfTheMonth from '../components/schools/SchoolOfTheMonth.vue';

OmegaUp.on('ready', function() {
  const payload = JSON.parse(
    document.getElementById('school-of-the-month-payload').innerText,
  );
  let SchoolOfTheMonth = new Vue({
    el: '#school-of-the-month',
    render: function(createElement) {
      return createElement('school-of-the-month', {
        props: {
          schoolsOfCurrentMonth: this.schoolsOfCurrentMonth,
          schoolsOfPreviousMonths: this.schoolsOfPreviousMonths,
          candidatesToSchoolOfTheMonth: this.candidatesToSchoolOfTheMonth,
          isMentor: this.isMentor,
          canChooseSchool: this.canChooseSchool,
          schoolIsSelected: this.schoolIsSelected,
        },
        on: {
          'select-school': function(schoolId) {
            API.School.selectSchoolOfTheMonth({
              school_id: schoolId,
            })
              .then(function() {
                UI.success(T.schoolOfTheMonthSelectedSuccessfully);
                SchoolOfTheMonth.schoolIsSelected = true;
              })
              .catch(UI.apiError);
          },
        },
      });
    },
    data: {
      schoolsOfCurrentMonth: payload.schoolsOfCurrentMonth,
      schoolsOfPreviousMonths: payload.schoolsOfPreviousMonths,
      candidatesToSchoolOfTheMonth: payload.candidatesToSchoolOfTheMonth,
      isMentor: payload.isMentor,
      canChooseSchool: payload.isMentor && payload.options.canChooseSchool,
      schoolIsSelected: payload.isMentor && payload.options.schoolIsSelected,
    },
    components: {
      'school-of-the-month': schoolOfTheMonth,
    },
  });
});
