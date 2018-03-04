import admin_Support from '../components/admin/Support.vue';
import {API, UI, OmegaUp, T} from '../omegaup.js';
import Vue from 'vue';

OmegaUp.on('ready', function() {
  var payload = JSON.parse(document.getElementById('payload').innerText);

  var adminSupport = new Vue({
    el: '#admin-support',
    render: function(createElement) {
      return createElement('omegaup-admin-support', {
        props: {},
        on: {},
      });
    },
    data: {},
    components: {
      'omegaup-admin-support': admin_Support,
    },
  });
});
