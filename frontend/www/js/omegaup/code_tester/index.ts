import { OmegaUp } from '../omegaup';
import { types } from '../api_types';
import CodeTesterMain from '../components/code-tester/Main.vue';
import Vue from 'vue';

OmegaUp.on('ready', () => {
  const payload = types.payloadParsers.CodeTesterPayload();

  new Vue({
    el: '#main-container',
    render: (createElement) =>
      createElement(CodeTesterMain, {
        props: {
          payload,
        },
      }),
  });
});
