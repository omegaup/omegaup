import { OmegaUp } from '../omegaup';
import { types } from '../api_types';
import * as api from '../api_transitional';
import * as ui from '../ui';
import T from '../lang';
import Vue from 'vue';
import coderofthemonth_List from '../components/coderofthemonth/List.vue';

OmegaUp.on('ready', () => {
  const payload = types.payloadParsers.CoderOfTheMonthPayload('payload');
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
            api.User.selectCoderOfTheMonth({
              username: coderUsername,
            })
              .then(function() {
                ui.success(
                  payload.category == 'all'
                    ? T.coderOfTheMonthSelectedSuccessfully
                    : T.coderOfTheMonthFemaleSelectedSuccessfully,
                );
                coderOfTheMonthList.coderIsSelected = true;
              })
              .catch(ui.apiError);
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
