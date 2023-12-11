import Vue from 'vue';
import certificate_Validation from '../components/certificate/Validation.vue';
import { OmegaUp } from '../omegaup';
import { types } from '../api_types';

OmegaUp.on('ready', () => {
  const payload = types.payloadParsers.CertificateValidationPayload();
  new Vue({
    el: '#main-container',
    components: {
      'omegaup-certificate-validation': certificate_Validation,
    },
    render: function (createElement) {
      return createElement('omegaup-certificate-validation', {
        props: {
          verificationCode: payload.verification_code,
          isValid: payload.valid,
          certificate: payload.certificate,
        },
      });
    },
  });
});
