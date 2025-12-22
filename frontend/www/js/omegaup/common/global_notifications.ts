import Vue from 'vue';
import GlobalNotifications from '../components/common/GlobalNotifications.vue';
import { OmegaUp } from '../omegaup';

OmegaUp.on('ready', () => {
  const mountPoint = document.getElementById('global-notifications');
  if (!mountPoint) {
    return;
  }

  const vm = new Vue({
    el: '#global-notifications',
    components: {
      'omegaup-global-notifications': GlobalNotifications,
    },
    render: function (createElement) {
      return createElement('omegaup-global-notifications');
    },
  });

  // Listen for ui-ready event emitted by GlobalNotifications component
  // This handles the legacy loading/root element visibility in a Vue-native way
  vm.$on('ui-ready', () => {
    const loadingEl = document.getElementById('loading');
    const rootEl = document.getElementById('root');
    if (loadingEl) loadingEl.style.display = 'none';
    if (rootEl) rootEl.style.display = 'block';
  });
});
