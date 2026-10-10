import admin_OperationsDashboard from '../components/admin/OperationsDashboard.vue';
import { OmegaUp } from '../omegaup';
import Vue from 'vue';

OmegaUp.on('ready', () => {
  new Vue({
    el: '#main-container',
    components: {
      'omegaup-admin-operations-dashboard': admin_OperationsDashboard,
    },
    render: function (createElement) {
      return createElement('omegaup-admin-operations-dashboard');
    },
  });
});
