declare module '@/third_party/js/pagedown/Markdown.Editor.js' {
  import * as Markdown from '@/third_party/js/pagedown/Markdown.Converter.js';

  export class Editor {
    constructor(markdownConverter: Markdown.Converter, idPostfix: string);

    getPostfix: () => string;
    getConverter: () => Markdown.Converter;
    run: () => void;
  }
}
