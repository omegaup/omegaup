import Vue from 'vue';
import certificate_Validation from '../components/certificate/Validation.vue';
import { OmegaUp } from '../omegaup';
import { types } from '../api_types';
import * as ui from '../ui';
import * as api from '../api';

OmegaUp.on('ready', () => {
  const payload = types.payloadParsers.CertificateValidationPayload();
  new Vue({
    el: '#main-container',
    components: {
      'omegaup-certificate-validation': certificate_Validation,
    },
    methods: {
      getPdfUrl: (verificationCode: string): string => {
        var file = '';
        api.Certificate.getCertificatePdf({
          verification_code: verificationCode,
        })
          .then((result) => {
            file = result.certificate;
          })
          .catch(ui.apiError);
        const decodedData = atob(file);
        const unicode = new Array(decodedData.length);
        for (let i = 0; i < decodedData.length; i++) {
          unicode[i] = decodedData.charCodeAt(i);
        }
        const byteArray = new Uint8Array(unicode);
        const blob = new Blob([byteArray], {
          type: 'application/pdf',
        });
        return window.URL.createObjectURL(blob);
      },
    },
    render: function (createElement) {
      return createElement('omegaup-certificate-validation', {
        props: {
          verificationCode: payload.verification_code,
          isValid: payload.valid,
          certificate: this.getPdfUrl(payload.verification_code),
        },
      });
    },
  });
});