import { OmegaUp } from '../omegaup';
import { types } from '../api_types';
import * as api from '../api';
import * as ui from '../ui';
import Vue from 'vue';

OmegaUp.on('ready', () => {
  const payload = types.payloadParsers.CollectionsDetailsPayload();

  const collectionDetails = new Vue({
    el: '#main-container',
    render: function (createElement) {
      return createElement('omegaup-collection-details', {
        props: {},
        on: {},
      });
    },
  });
});
