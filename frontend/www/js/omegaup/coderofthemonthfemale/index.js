import { API, UI, OmegaUp, T } from '../omegaup.js';
import Vue from 'vue';
import coderofthemonthfemale_List from '../components/coderofthemonthfemale/List.vue';

OmegaUp.on('ready', function() {
  let payload = JSON.parse(document.getElementById('payload').innerText);
  let coderOfTheMonthFemaleList = new Vue({
    el: '#coder-of-the-month-female',
    render: function(createElement) {
      return createElement('omegaup-coder-of-the-month-female-list', {
        props: {
          codersOfCurrentMonthFemale: this.codersOfCurrentMonthFemale,
          codersOfPreviousMonthFemale: this.codersOfPreviousMonthFemale,
          candidatesToCoderOfTheMonthFemale: this
            .candidatesToCoderOfTheMonthFemale,
          isMentor: this.isMentor,
          canChooseCoder: this.canChooseCoder,
          coderIsSelected: this.coderIsSelected,
        },
        on: {
          'select-coder': function(coderUsername) {
            API.User.selectCoderOfTheMonth({
              username: coderUsername,
            })
              .then(function(data) {
                UI.success(T.coderOfTheMonthFemaleSelectedSuccessfully);
                coderOfTheMonthFemaleList.coderIsSelected = true;
              })
              .fail(UI.apiError);
          },
        },
      });
    },
    data: {
      codersOfCurrentMonthFemale: payload.codersOfCurrentMonth,
      codersOfPreviousMonthFemale: payload.codersOfPreviousMonth,
      isMentor: payload.isMentor,
      candidatesToCoderOfTheMonthFemale: payload.candidatesToCoderOfTheMonth,
      canChooseCoder: payload.isMentor && payload.options.canChooseCoder,
      coderIsSelected: payload.isMentor && payload.options.coderIsSelected,
    },
    components: {
      'omegaup-coder-of-the-month-female-list': coderofthemonthfemale_List,
    },
  });
});
