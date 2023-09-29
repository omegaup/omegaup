import { OmegaUp } from '../omegaup';
import { types } from '../api_types';
import Vue from 'vue';
import certificate_Mine from '../components/certificate/Mine.vue';
import * as ui from '../ui';
import * as api from '../api';

OmegaUp.on('ready', async () => {
  const payload = types.payloadParsers.CertificateListMinePayload();
  new Vue({
    el: '#main-container',
    components: {
      'omegaup-certificate-mine': certificate_Mine,
    },
    methods: {
      downloadPdfFile: (fileName: string, file: string): void => {
        const decodedData = atob(file);
        const unicode = new Array(decodedData.length);
        for (let i = 0; i < decodedData.length; i++) {
          unicode[i] = decodedData.charCodeAt(i);
        }
        const byteArray = new Uint8Array(unicode);
        const blob = new Blob([byteArray], {
          type: 'application/pdf',
        });
        const link = document.createElement('a');
        link.href = window.URL.createObjectURL(blob);
        link.download = fileName;
        link.click();
      },
    },
    render: function (createElement) {
      return createElement('omegaup-certificate-mine', {
        props: {
          certificates: payload.certificates,
        },
        on: {
          'download-pdf-certificate': ({
            verificationCode,
            name,
          }: {
            verificationCode: string;
            name: string;
          }) => {
            api.Certificate.getCertificatePdf({
              verification_code: verificationCode,
            })
              .then((result) => {
                this.downloadPdfFile(
                  `certificate_${name}.pdf`,
                  result.certificate,
                );
              })
              .catch(ui.apiError);
          },
        },
      });
    },
  });
});
