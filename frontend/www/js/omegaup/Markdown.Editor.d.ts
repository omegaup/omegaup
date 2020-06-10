declare module '@/third_party/js/pagedown/Markdown.Editor.js' {
  import * as Markdown from '@/third_party/js/pagedown/Markdown.Converter.js';

  interface Hooks {
    set: (eventName: string, callback: any) => void;
    chain: (eventName: string, callback: any) => void;
  }

  export class Editor {
    hooks: Hooks;

    constructor(markdownConverter: Markdown.Converter, idPostfix?: string);

    getPostfix: () => string;
    getConverter: () => Markdown.Converter;
    run: () => void;
    refreshPreview(): () => void;
  }
}
