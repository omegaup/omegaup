import '../lang';
import admin_Settings from '../components/admin/Settings.vue';
import { OmegaUp } from '../omegaup';
import Vue from 'vue';

OmegaUp.on('ready', () => {
  new Vue({
    el: '#main-container',
    components: {
      'omegaup-admin-settings': admin_Settings,
    },
    render: function (createElement) {
      return createElement('omegaup-admin-settings');
    },
  });
});
