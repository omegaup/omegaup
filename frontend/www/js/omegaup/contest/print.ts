import * as markdown from '../markdown';
import { OmegaUp } from '../omegaup';

declare global {
  namespace MathJax {
    namespace Hub {
      function Queue(params: any[]): void;
    }
  }
}

(() => {
  const markdownConverter = markdown.markdownConverter({ preview: true });

  document.querySelectorAll('div.problem').forEach(problem => {
    const output = <HTMLElement>problem.querySelector('div.statement');
    const payload = JSON.parse(
      (<HTMLElement>problem.querySelector('script.payload')).innerText,
    );

    output.innerHTML = markdownConverter.makeHtmlWithImages(
      payload.statement.markdown,
      payload.statement.images,
      payload.settings,
    );
    MathJax.Hub.Queue(['Typeset', MathJax.Hub, output]);
  });
})();
