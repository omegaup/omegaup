import common_Footer from '../components/common/Footer.vue';
import { OmegaUp, T} from '../omegaup.js';
import Vue from 'vue';

OmegaUp.on('ready', function() {
  let commonFooter = new Vue({
    el: '#common-footer',
    render: function(createElement) {
      return createElement('omegaup-common-footer', {});
    },
    components: {
      'omegaup-common-footer': common_Footer,
    },
  });
});
