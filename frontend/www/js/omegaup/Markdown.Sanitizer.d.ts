declare module '@/third_party/js/pagedown/Markdown.Sanitizer.js' {
  import * as Markdown from '@/third_party/js/pagedown/Markdown.Converter.js';

  export function getSanitizingConverter(): Markdown.Converter;
}
