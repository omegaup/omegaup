import {API, UI, OmegaUp, T} from '../omegaup.js';
import Vue from 'vue';
import coders_of_the_month from '../components/coderofthemonth/CodersOfTheMonth.vue';

OmegaUp.on('ready', function() {
  const payload = JSON.parse(document.getElementById('payload').innerText);
  let codersOfTheMonth = new Vue({
    el: '#mentor-coders-of-the-month',
    render: function(createElement) {
      return createElement('coders-of-the-month', {
        props: {
          coders: this.bestCoders,
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
                  codersOfTheMonth.coderIsSelected = true;
                })
                .fail(UI.apiError);
          }
        }
      });
    },
    data: {
      bestCoders: payload.bestCoders,
      canChooseCoder: payload.canChooseCoder,
      coderIsSelected: payload.coderIsSelected,
    },
    components: {'coders-of-the-month': coders_of_the_month}
  });
});
