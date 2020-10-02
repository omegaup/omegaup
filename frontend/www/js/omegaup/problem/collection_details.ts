import Vue from 'vue';
import collection_Details from '../components/problem/CollectionDetails.vue';
import { types } from '../api_types';
import { OmegaUp } from '../omegaup';

OmegaUp.on('ready', () => {
  const payload = types.payloadParsers.CollectionDetailsPayload();
  const CollectionDetails = new Vue({
    el: '#main-container',
    render: function (createElement) {
      return createElement('omegaup-collection-details', {
        props: {
          data: payload,
        },
      });
    },
    components: {
      'omegaup-collection-details': collection_Details,
    },
  });
});
