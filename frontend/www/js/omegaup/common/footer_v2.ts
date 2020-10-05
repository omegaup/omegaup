import Footer from '../components/common/Footerv2.vue';
import { OmegaUp } from '../omegaup';
import { types } from '../api_types';
import Vue from 'vue';

OmegaUp.on('ready', () => {
  const payload = types.payloadParsers.CommonPayload();

  new Vue({
    el: '#common-footer',
    components: {
      'omegaup-common-footer': Footer,
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
