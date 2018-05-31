import {API, UI, OmegaUp, T} from '../omegaup.js';
import Vue from 'vue';
import virtual from '../components/arena/Virtual.vue';

OmegaUp.on('ready', function() {
  let contestAlias = /\/arena\/([^\/]+)\/virtual/.exec(window.location.pathname)[1]
  let detail;
  API.Contest.publicDetails({contest_alias: contestAlias})
      .then(function(response) {
          let detail = response;
          let virtual_ = new Vue({
            el: '#virtual',
            render: function(createElement) {
                return createElement('virtual', {
                    props: {
                        detail: detail 
                    }
                });
            },
            components: {'virtual': virtual}
          });
      }).fail(UI.apiError);
});
