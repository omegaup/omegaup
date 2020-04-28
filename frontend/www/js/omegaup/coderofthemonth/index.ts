import { OmegaUp } from '../omegaup';
import API from '../api.js';
import { types } from '../api_types';
import * as api from '../api_transitional';
import * as UI from '../ui';
import T from '../lang';
import Vue from 'vue';
import coderofthemonth_List from '../components/coderofthemonth/List.vue';

OmegaUp.on('ready', () => {
  const payload = types.payloadParsers.CoderOfTheMonthPayload('payload');
  console.log(payload);
  let coderOfTheMonthList = new Vue({
    el: '#main-container',
    render: function(createElement) {
      return createElement('omegaup-coder-of-the-month-list', {
        props: {
          codersOfCurrentMonth: payload.codersOfCurrentMonth,
          codersOfPreviousMonth: payload.codersOfPreviousMonth,
          candidatesToCoderOfTheMonth: payload.candidatesToCoderOfTheMonth,
          isMentor: payload.isMentor,
          canChooseCoder:
            payload.isMentor &&
            payload.options &&
            payload.options.canChooseCoder,
          coderIsSelected: this.coderIsSelected,
          category: payload.category,
        },
        on: {
          'select-coder': function(coderUsername: string) {
            API.User.selectCoderOfTheMonth({
              username: coderUsername,
            })
              .then(function() {
                UI.success(
                  payload.category == 'all'
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
      coderIsSelected:
        payload.isMentor && payload.options && payload.options.coderIsSelected,
    },
    components: {
      'omegaup-coder-of-the-month-list': coderofthemonth_List,
    },
  });
});
