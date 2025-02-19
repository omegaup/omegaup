import common_MarkdownViewer from '../components/common/MarkdownViewer.vue';
import Vue from 'vue';
import { OmegaUp } from '../omegaup';
import { types } from '../api_types';

OmegaUp.on('ready', () => {
  const payload = types.payloadParsers.UserDocumentPayload();
  new Vue({
    el: '#main-container',
    components: {
      'omegaup-common-docs': common_MarkdownViewer,
    },
    render: function (createElement) {
      return createElement('omegaup-common-docs', {
        props: {
          content: payload.content,
          filename: payload.filename,
        },
      });
    },
  });
});
