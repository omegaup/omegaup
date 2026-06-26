import Vue from 'vue';
import user_VerificationParentalToken from '../components/user/VerificationParentalToken.vue';
import { OmegaUp } from '../omegaup';
import { types } from '../api_types';

OmegaUp.on('ready', () => {
  const payload =
    types.payloadParsers.VerificationParentalTokenDetailsPayload();

  new Vue({
    el: '#main-container',
    components: {
      'omegaup-verification-parental-token': user_VerificationParentalToken,
    },
    render: function (createElement) {
      return createElement('omegaup-verification-parental-token', {
        props: {
          hasParentalVerificationToken: payload.hasParentalVerificationToken,
          message: payload.message,
        },
      });
    },
  });
});
