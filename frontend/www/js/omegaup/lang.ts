import * as lang_en from './lang.en';
import * as lang_es from './lang.es';
import * as lang_pt from './lang.pt';
import * as lang_pseudo from './lang.pseudo';

const T = (function () {
  const head =
    (document && document.querySelector && document.querySelector('head')) ||
    null;

  switch ((head && head.dataset && head.dataset.locale) || 'es') {
    case 'pseudo':
      return lang_pseudo.default;

    case 'pt':
      return lang_pt.default;

    case 'en':
      return lang_en.default;

    case 'es':
    default:
      return lang_es.default;
  }
})();

export { T as default };
