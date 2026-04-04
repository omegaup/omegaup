import { OmegaUp } from '../omegaup';
import { types } from '../api_types';
import Vue from 'vue';
import libinteractive_Gen from '../components/libinteractive/Gen.vue';

OmegaUp.on('ready', () => {
  const payload = types.payloadParsers.LibinteractiveGenPayload();
  new Vue({
    el: '#main-container',
    components: {
      'omegaup-libinteractive-gen': libinteractive_Gen,
    },
    render: function (createElement) {
      return createElement('omegaup-libinteractive-gen', {
        props: {
          error: payload.error,
          language: payload.language,
          os: payload.os,
          name: payload.name,
          idl: payload.idl,
        },
      });
    },
  });
});
