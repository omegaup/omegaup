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
  const payload =
      JSON.parse((<HTMLElement>document.getElementById('payload')).innerText);
  const markdownConverter = markdown.markdownConverter({preview: true});

  const statement = <HTMLElement>document.querySelector('div.statement');
  statement.innerHTML = markdownConverter.makeHtmlWithImages(
      payload.statement.markdown,
      payload.statement.images,
      payload.settings,
  );
  MathJax.Hub.Queue(['Typeset', MathJax.Hub, statement]);
})();
