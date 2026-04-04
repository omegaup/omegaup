import { OmegaUp } from '../omegaup';
import { types } from '../api_types';
import * as api from '../api';
import * as ui from '../ui';
import T from '../lang';
import Vue from 'vue';
import schoolOfTheMonth_List from '../components/schoolofthemonth/List.vue';

OmegaUp.on('ready', () => {
  const payload = types.payloadParsers.SchoolOfTheMonthPayload();
  const schoolOfTheMonthList = new Vue({
    el: '#main-container',
    components: {
      'school-of-the-month-list': schoolOfTheMonth_List,
    },
    data: () => ({
      schoolIsSelected:
        payload.isMentor && payload.options && payload.options.schoolIsSelected,
    }),
    render: function (createElement) {
      return createElement('school-of-the-month-list', {
        props: {
          schoolsOfPreviousMonth: payload.schoolsOfPreviousMonth,
          schoolsOfPreviousMonths: payload.schoolsOfPreviousMonths,
          candidatesToSchoolOfTheMonth: payload.candidatesToSchoolOfTheMonth,
          isMentor: payload.isMentor,
          canChooseSchool:
            payload.isMentor &&
            payload.options &&
            payload.options.canChooseSchool,
          schoolIsSelected: this.schoolIsSelected,
        },
        on: {
          'select-school': function (schoolId: number) {
            api.School.selectSchoolOfTheMonth({
              school_id: schoolId,
            })
              .then(() => {
                ui.success(T.schoolOfTheMonthSelectedSuccessfully);
                schoolOfTheMonthList.schoolIsSelected = true;
              })
              .catch(ui.apiError);
          },
        },
      });
    },
  });
});
