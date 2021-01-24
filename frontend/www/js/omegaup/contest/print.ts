import Vue from 'vue';

import type { types } from '../api_types';

import omegaup_Markdown from '../components/Markdown.vue';

(() => {
  document.querySelectorAll('div.problem').forEach((problem) => {
    const problemDetails = JSON.parse(
      (problem.querySelector('script.payload') as HTMLElement).innerText,
    ) as types.ProblemDetails;

    new Vue({
      el: problem.querySelector('div.statement') as HTMLElement,
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
  });
})();
