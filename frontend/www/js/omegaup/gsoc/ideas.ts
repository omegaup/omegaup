import { OmegaUp } from '../omegaup';
import Vue from 'vue';
import IdeaList from '../components/gsoc/IdeaList.vue';

OmegaUp.on('ready', () => {
  new Vue({
    el: '#main-container',
    components: {
      'omegaup-gsoc-ideas': IdeaList,
    },
    render: (createElement) => {
      return createElement('omegaup-gsoc-ideas', {
        props: {
          isAdmin: false,
        },
      });
    },
  });
});
