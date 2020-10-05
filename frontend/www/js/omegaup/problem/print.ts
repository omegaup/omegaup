import Vue from 'vue';

import { types } from '../api_types';

import omegaup_Markdown from '../components/Markdown.vue';

(() => {
  const problemDetails = <types.ProblemDetails>(
    JSON.parse((<HTMLElement>document.getElementById('payload')).innerText)
  );

  new Vue({
    el: <HTMLElement>document.querySelector('div.statement'),
    components: {
      'omegaup-markdown': omegaup_Markdown,
    },
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
  });
})();
