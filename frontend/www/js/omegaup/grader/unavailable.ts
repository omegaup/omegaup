import Vue from 'vue';
import common_ViewUnavailable from '../components/common/ViewUnavailable.vue';
import T from '../lang';
import { OmegaUp } from '../omegaup';

OmegaUp.on('ready', () => {
  new Vue({
    el: '#main-container',
    render: (createElement) =>
      createElement(common_ViewUnavailable, {
        props: {
          description: T.ephemeralGraderDisabled,
        },
      }),
  });
});
