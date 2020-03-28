import { OmegaUp } from '../omegaup';
import API from '../api.js';
import * as UI from '../ui';
import T from '../lang';
import Vue from 'vue';
import coderofthemonth_List from '../components/coderofthemonth/List.vue';

OmegaUp.on('ready', function() {
  let payload = JSON.parse(document.getElementById('payload').innerText);
  let coderOfTheMonthList = new Vue({
    el: '#coder-of-the-month',
    render: function(createElement) {
      return createElement('omegaup-coder-of-the-month-list', {
        props: {
          codersOfCurrentMonth: this.codersOfCurrentMonth,
          codersOfPreviousMonth: this.codersOfPreviousMonth,
          candidatesToCoderOfTheMonth: this.candidatesToCoderOfTheMonth,
          isMentor: this.isMentor,
          canChooseCoder: this.canChooseCoder,
          coderIsSelected: this.coderIsSelected,
          category: this.category,
        },
        on: {
          'select-coder': function(coderUsername) {
            API.User.selectCoderOfTheMonth({
              username: coderUsername,
            })
              .then(function(data) {
                UI.success(
                  category == 'all'
                    ? T.coderOfTheMonthSelectedSuccessfully
                    : T.coderOfTheMonthFemaleSelectedSuccessfully,
                );
                coderOfTheMonthList.coderIsSelected = true;
              })
              .catch(UI.apiError);
          },
        },
      });
    },
    data: {
      codersOfCurrentMonth: payload.codersOfCurrentMonth,
      codersOfPreviousMonth: payload.codersOfPreviousMonth,
      isMentor: payload.isMentor,
      candidatesToCoderOfTheMonth: payload.candidatesToCoderOfTheMonth,
      canChooseCoder: payload.isMentor && payload.options.canChooseCoder,
      coderIsSelected: payload.isMentor && payload.options.coderIsSelected,
      category: payload.category,
    },
    components: { 'omegaup-coder-of-the-month-list': coderofthemonth_List },
  });
});
