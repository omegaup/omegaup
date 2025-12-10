import omegaup_Footer from '../components/common/Footer.vue';
import { OmegaUp } from '../omegaup';
import { types } from '../api_types';
import Vue from 'vue';

OmegaUp.on('ready', () => {
  const payload = types.payloadParsers.CommonPayload();

  const commonFooterExists = document.getElementById('common-footer');
  if (!commonFooterExists) {
    return;
  }
  new Vue({
    el: '#common-footer',
    components: {
      'omegaup-common-footer': omegaup_Footer,
    },
    render: function (createElement) {
      return createElement('omegaup-common-footer', {
        props: {
          isLoggedIn: (payload && payload.isLoggedIn) || false,
          omegaUpLockDown: (payload && payload.omegaUpLockDown) || false,
        },
      });
    },
  });
});
