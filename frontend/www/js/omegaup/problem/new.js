import Vue from 'vue';
import problem_New from '../components/problem/Form.vue';
import { OmegaUp } from '../omegaup';
import * as ui from '../ui';
import T from '../lang';
import API from '../api.js';

OmegaUp.on('ready', function() {
  const payload = JSON.parse(document.getElementById('payload').innerText);
  let problemNew = new Vue({
    el: '#problem-new',
    render: function(createElement) {
      return createElement('omegaup-problem-new', {
        props: {
          data: this.data,
        },
        on: {
          'alias-in-use': alias => {
            API.Problem.details({ problem_alias: alias })
              .then(data => {
                if (!data.exists) {
                  ui.dismissNotifications();
                  return;
                }
                ui.error(
                  ui.formatString(T.aliasAlreadyInUse, {
                    alias: ui.escape(alias),
                  }),
                );
              })
              .catch(ui.apiError);
          },
        },
      });
    },
    data: {
      data: payload,
    },
    components: {
      'omegaup-problem-new': problem_New,
    },
  });
});
