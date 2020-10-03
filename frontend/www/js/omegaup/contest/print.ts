import Vue from 'vue';

import { types } from '../api_types';

import omegaup_Markdown from '../components/Markdown.vue';

(() => {
  document.querySelectorAll('div.problem').forEach((problem) => {
    const problemDetails = <types.ProblemDetails>(
      JSON.parse(
        (<HTMLElement>problem.querySelector('script.payload')).innerText,
      )
    );

    new Vue({
      el: <HTMLElement>problem.querySelector('div.statement'),
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
  });
})();
