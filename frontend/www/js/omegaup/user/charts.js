import user_Charts from '../components/user/Charts.vue';
import {API, UI, OmegaUp, T} from '../omegaup.js';
import Vue from 'vue';

OmegaUp.on('ready', function() {
  let username = $('#username').attr('data-username');
  omegaup.API.User.stats({username: username})
      .then(function(data) {
        let userCharts = new Vue({
          el: '#omegaup-user-charts',
          render: function(createElement) {
            return createElement('omegaup-user-charts', {
              props: {data: data, username: username},
            });
          },
          components: {
            'omegaup-user-charts': user_Charts,
          },
        });
      })
      .fail(omegaup.UI.apiError);
});
