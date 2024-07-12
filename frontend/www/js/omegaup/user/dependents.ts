import user_Dependents from '../components/user/Dependents.vue';
import Vue from 'vue';
import { OmegaUp } from '../omegaup';
import { types } from '../api_types';

OmegaUp.on('ready', () => {
  const payload = types.payloadParsers.UserDependentsPayload();
  new Vue({
    el: '#main-container',
    components: {
      'omegaup-user-dependents': user_Dependents,
    },
    render: function (createElement) {
      return createElement('omegaup-user-dependents', {
        props: {
          dependents: payload.dependents,
        },
      });
    },
  });
});
