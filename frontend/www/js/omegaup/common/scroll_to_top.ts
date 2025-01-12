import omegaup_ScrollToTop from '../components/common/ScrollToTop.vue';
import { OmegaUp } from '../omegaup';
import Vue from 'vue';

OmegaUp.on('ready', () => {
  const scrollToTopExists = document.getElementById('scroll-to-top');
  if (!scrollToTopExists) {
    return;
  }
  new Vue({
    el: '#scroll-to-top',
    components: {
      'omegaup-scroll-to-top': omegaup_ScrollToTop,
    },
    render: function (createElement) {
      return createElement('omegaup-scroll-to-top');
    },
  });
});
