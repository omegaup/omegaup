import common_Footer from '../components/common/Footer.vue';
import { OmegaUp } from '../omegaup';
import { types } from '../api_types';
import Vue from 'vue';

OmegaUp.on('ready', () => {
  const payload = types.payloadParsers.CommonPayload('payload');
  let isLoggedIn = false;
  let omegaUpLockDown = false;
  if (
    typeof payload !== 'undefined' &&
    typeof payload.isLoggedIn !== 'undefined'
  ) {
    isLoggedIn = payload.isLoggedIn;
    omegaUpLockDown = payload.omegaUpLockDown;
  }

  const commonFooter = new Vue({
    el: '#common-footer',
    render: function(createElement) {
      return createElement('omegaup-common-footer', {
        props: {
          isLoggedIn: this.isLoggedIn,
          omegaUpLockDown: this.omegaUpLockDown,
        },
      });
    },
    data: {
      isLoggedIn: isLoggedIn,
      omegaUpLockDown: omegaUpLockDown,
    },
    components: {
      'omegaup-common-footer': common_Footer,
    },
  });
});
