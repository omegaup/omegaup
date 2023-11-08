import { OmegaUp } from '../omegaup';
import { types } from '../api_types';
import * as ui from '../ui';
import T from '../lang';
import Vue from 'vue';
import certificate_Mine from '../components/certificate/Mine.vue';

OmegaUp.on('ready', () => {
  const payload = types.payloadParsers.CertificateListMinePayload();
  new Vue({
    el: '#main-container',
    components: {
      'omegaup-certificate-mine': certificate_Mine,
    },
    render: function (createElement) {
      return createElement('omegaup-certificate-mine', {
        props: {
          certificates: payload.certificates,
          location: window.location.origin,
        },
        on: {
          'show-copy-message': (): void => {
            ui.success(T.certificateListMineLinkCopiedToClipboard);
          },
          'show-download-message': (): void => {
            ui.success(T.certificateListMineFileDownloaded);
          },
        },
      });
    },
  });
});
