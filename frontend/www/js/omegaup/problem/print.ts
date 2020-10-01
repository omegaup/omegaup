import Vue from 'vue';

import { types } from '../api_types';

import omegaup_Markdown from '../components/Markdown.vue';

(() => {
  const problemDetails = <types.ProblemDetails>(
    JSON.parse((<HTMLElement>document.getElementById('payload')).innerText)
  );

  const contestIntro = new Vue({
    el: <HTMLElement>document.querySelector('div.statement'),
    render: function (createElement) {
      return createElement('omegaup-markdown', {
        props: {
          markdown: problemDetails.statement.markdown,
          imageMapping: problemDetails.statement.images,
          sourceMapping: problemDetails.statement.sources,
          problemSettings: problemDetails.settings,
        },
      });
    },
    components: {
      'omegaup-markdown': omegaup_Markdown,
    },
  });
})();
