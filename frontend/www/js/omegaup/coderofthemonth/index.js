import {API, UI, OmegaUp, T} from '../omegaup.js';
import Vue from 'vue';
import coder_of_the_month from '../components/coderofthemonth/CoderOfTheMonth.vue';

OmegaUp.on('ready', function() {
  let payload = JSON.parse(document.getElementById('payload').innerText);
  let coderOfTheMonth = new Vue({
    el: '#coder-of-the-month',
    render: function(createElement) {
      return createElement('coder-of-the-month', {
        props: {
          codersOfCurrentMonth: this.codersOfCurrentMonth,
          codersOfPreviousMonth: this.codersOfPreviousMonth,
          candidatesToCoderOfTheMonth: this.candidatesToCoderOfTheMonth,
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
                  UI.success(T.coderOfTheMonthSelectedSuccessfully);
                  coderOfTheMonth.coderIsSelected = true;
                })
                .fail(UI.apiError);
          }
        },
      });
    },
    data: {
      codersOfCurrentMonth: payload.codersOfCurrentMonth,
      codersOfPreviousMonth: payload.codersOfPreviousMonth,
      isMentor: payload.isMentor,
      candidatesToCoderOfTheMonth:
          payload.isMentor ? payload.options.bestCoders : [],
      canChooseCoder: payload.isMentor ? payload.options.canChooseCoder : false,
      coderIsSelected: payload.isMentor ? payload.options.coderIsSelected :
                                          false,
    },
    components: {'coder-of-the-month': coder_of_the_month}
  });
});
