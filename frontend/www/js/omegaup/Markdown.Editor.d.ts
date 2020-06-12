declare module '@/third_party/js/pagedown/Markdown.Editor.js' {
  import * as Markdown from '@/third_party/js/pagedown/Markdown.Converter.js';

  interface Hooks {
    set: (eventName: string, callback: (text?: string, ...args: any[]) => string | () ) => void;
    chain: (eventName: string, callback: (text?: string, ...args: any[]) => string | () ) => void;
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
