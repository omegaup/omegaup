import Vue from 'vue';
import common_GlobalNotifications from '../components/common/GlobalNotifications.vue';
import { OmegaUp } from '../omegaup';

OmegaUp.on('ready', () => {
  const mountPoint = document.getElementById('global-notifications');
  if (!mountPoint) {
    return;
  }

  new Vue({
    el: '#global-notifications',
    components: {
      'omegaup-global-notifications': common_GlobalNotifications,
    },
    render: function (createElement) {
      return createElement('omegaup-global-notifications');
    },
  });
});
