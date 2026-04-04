import Vue from 'vue';
import certificate_Details from '../components/certificate/Details.vue';
import { OmegaUp } from '../omegaup';
import { types } from '../api_types';

OmegaUp.on('ready', () => {
  const payload = types.payloadParsers.CertificateDetailsPayload();
  new Vue({
    el: '#main-container',
    components: {
      'omegaup-certificate-details': certificate_Details,
    },
    render: function (createElement) {
      return createElement('omegaup-certificate-details', {
        props: {
          uuid: payload.uuid,
        },
      });
    },
  });
});
